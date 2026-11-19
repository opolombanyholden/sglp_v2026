<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copie toutes les données de l'ancienne base MySQL (connexion `mysql_source`)
 * vers la base PostgreSQL courante, dont le schéma a été créé par `php artisan migrate`.
 *
 * Les clés étrangères sont retirées pendant la copie (références circulaires et
 * auto-références), puis recréées : PostgreSQL revérifie alors toutes les données.
 */
class CopierDepuisMysql extends Command
{
    protected $signature = 'sglp:copier-depuis-mysql
                            {--force : Vider les tables PostgreSQL qui contiennent déjà des données}
                            {--lot=500 : Nombre de lignes insérées par requête}';

    protected $description = 'Copie les données de la base MySQL (mysql_source) vers PostgreSQL';

    /** Tables gérées par Laravel, jamais copiées. */
    private const IGNOREES = ['migrations'];

    public function handle(): int
    {
        $pg = DB::connection();
        if ($pg->getDriverName() !== 'pgsql') {
            $this->error('La connexion par défaut doit être PostgreSQL (DB_CONNECTION=pgsql).');
            return self::FAILURE;
        }
        $src = DB::connection('mysql_source');
        // Le journal des requêtes garderait chaque lot (PDF compris) en mémoire
        $pg->disableQueryLog();
        $src->disableQueryLog();
        $pg->unsetEventDispatcher();
        $src->unsetEventDispatcher();

        $tablesPg = collect($pg->select("SELECT tablename FROM pg_tables WHERE schemaname = current_schema() ORDER BY tablename"))
            ->pluck('tablename')->diff(self::IGNOREES)->values();
        $tablesMy = collect($src->select('SHOW TABLES'))->map(fn ($r) => array_values((array) $r)[0]);

        $manquantes = $tablesMy->diff($tablesPg)->diff(self::IGNOREES);
        if ($manquantes->isNotEmpty()) {
            $this->error('Tables MySQL absentes du schéma PostgreSQL : ' . $manquantes->implode(', '));
            return self::FAILURE;
        }

        $nonVides = $tablesPg->filter(fn ($t) => $pg->table($t)->exists());
        if ($nonVides->isNotEmpty() && !$this->option('force')) {
            $this->error('Tables PostgreSQL déjà remplies : ' . $nonVides->implode(', ') . '. Relancer avec --force pour les vider.');
            return self::FAILURE;
        }

        // 1. Retirer les clés étrangères en gardant leur définition
        $fks = $pg->select("SELECT conrelid::regclass::text AS tbl, conname, pg_get_constraintdef(oid) AS def
                            FROM pg_constraint WHERE contype = 'f' AND connamespace = current_schema()::regnamespace");
        $this->info(count($fks) . ' clés étrangères retirées pendant la copie.');

        $pg->beginTransaction();
        try {
            foreach ($fks as $fk) {
                $pg->statement("ALTER TABLE {$fk->tbl} DROP CONSTRAINT \"{$fk->conname}\"");
            }
            if ($nonVides->isNotEmpty()) {
                $pg->statement('TRUNCATE ' . $nonVides->map(fn ($t) => "\"$t\"")->implode(', ') . ' RESTART IDENTITY');
            }

            // 2. Copier chaque table
            foreach ($tablesPg as $table) {
                if (!$tablesMy->contains($table)) {
                    $this->line("  $table : absente de MySQL, laissée vide");
                    continue;
                }
                $n = $this->copierTable($src, $pg, $table);
                $this->line(sprintf('  %-38s %7d lignes', $table, $n));
            }

            // 3. Réparer les références orphelines héritées de MySQL (imports faits
            //    avec FOREIGN_KEY_CHECKS=0), puis recréer les clés étrangères
            $this->reparerOrphelins($pg, $fks);
            foreach ($fks as $fk) {
                $pg->statement("ALTER TABLE {$fk->tbl} ADD CONSTRAINT \"{$fk->conname}\" {$fk->def}");
            }

            // 4. Recaler les séquences des clés auto-incrémentées
            $seqs = $pg->select("SELECT c.table_name, c.column_name, pg_get_serial_sequence(quote_ident(c.table_name), c.column_name) AS seq
                                 FROM information_schema.columns c
                                 WHERE c.table_schema = current_schema() AND c.column_default LIKE 'nextval(%'");
            foreach ($seqs as $s) {
                $pg->statement("SELECT setval('{$s->seq}', COALESCE((SELECT MAX(\"{$s->column_name}\") FROM \"{$s->table_name}\"), 0) + 1, false)");
            }

            $pg->commit();
        } catch (\Throwable $e) {
            $pg->rollBack();
            $this->error('Copie annulée, aucune donnée modifiée : ' . $e->getMessage());
            return self::FAILURE;
        }

        // 5. Contrôle : mêmes nombres de lignes des deux côtés
        $ecarts = 0;
        foreach ($tablesPg as $table) {
            if (!$tablesMy->contains($table)) continue;
            $a = $src->table($table)->count();
            $b = $pg->table($table)->count();
            if ($a !== $b) {
                $this->error("  Écart sur $table : MySQL $a, PostgreSQL $b");
                $ecarts++;
            }
        }
        if ($ecarts) {
            return self::FAILURE;
        }
        $this->info('Copie terminée : nombres de lignes identiques sur les ' . $tablesPg->count() . ' tables.');
        return self::SUCCESS;
    }

    private function copierTable($src, $pg, string $table): int
    {
        $colonnes = collect($pg->select("SELECT column_name, data_type FROM information_schema.columns
                                         WHERE table_schema = current_schema() AND table_name = ?", [$table]))
            ->pluck('data_type', 'column_name');
        $colonnesMy = collect(Schema::connection('mysql_source')->getColumnListing($table));
        $communes = $colonnesMy->filter(fn ($c) => $colonnes->has($c))->values();

        $lot = max(1, (int) $this->option('lot'));
        // Les grosses tables (PDF en texte, etc.) sont copiées par petits lots
        $taille = (int) ($src->selectOne('SELECT data_length AS d FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?', [$table])->d ?? 0);
        $nbLignes = max(1, $src->table($table)->count());
        if ($taille / $nbLignes > 100_000) {
            $lot = min($lot, 20);
        }

        // Pagination par identifiant (id > dernier) : OFFSET devient très lent sur
        // les grosses tables (nip_database ≈ 1 million de lignes en production)
        $parId = $communes->contains('id')
            && in_array($colonnes['id'] ?? null, ['bigint', 'integer', 'smallint'], true);
        $ordre = $parId ? 'id' : $communes->first();
        $total = 0;
        $offset = 0;
        $dernierId = null;
        while (true) {
            $requete = $src->table($table)->select($communes->all())->orderBy($ordre)->limit($lot);
            if ($parId) {
                if ($dernierId !== null) $requete->where('id', '>', $dernierId);
            } else {
                $requete->offset($offset);
            }
            $lignes = $requete->get();
            if ($lignes->isEmpty()) break;
            $dernierId = $lignes->last()->id ?? null;
            $rows = $lignes->map(function ($r) use ($colonnes) {
                $r = (array) $r;
                foreach ($r as $c => $v) {
                    $r[$c] = $this->convertir($v, $colonnes[$c]);
                }
                return $r;
            })->all();
            $pg->table($table)->insert($rows);
            $total += count($rows);
            $offset += $lot;
            if ($this->output->isVeryVerbose()) {
                $this->line(sprintf('    %s +%d (mémoire %d Mo)', $table, $total, memory_get_usage() / 1048576));
            }
        }
        return $total;
    }

    /**
     * Les organisations dont la commune pointe vers un identifiant disparu (référentiel
     * renuméroté) sont rattachées par le nom saisi dans `ville_commune`. Toute autre
     * référence orpheline sur une colonne facultative est mise à NULL ; une référence
     * orpheline sur une colonne obligatoire fait échouer la copie.
     */
    private function reparerOrphelins($pg, array $fks): void
    {
        $n = $pg->update("UPDATE organisations o SET commune_ville_ref_id = c.id
            FROM communes_villes c
            WHERE o.commune_ville_ref_id IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM communes_villes x WHERE x.id = o.commune_ville_ref_id)
              AND lower(trim(c.nom)) = lower(trim(o.ville_commune))
              AND (SELECT count(*) FROM communes_villes c2 WHERE lower(trim(c2.nom)) = lower(trim(o.ville_commune))) = 1");
        if ($n) {
            $this->warn("  $n organisation(s) : commune inexistante, rattachée par son nom (ville_commune).");
        }

        foreach ($fks as $fk) {
            // Uniquement les clés sur une colonne : FOREIGN KEY (col) REFERENCES tbl(col)
            if (!preg_match('/^FOREIGN KEY \((\w+)\) REFERENCES (\w+)\((\w+)\)/', $fk->def, $m)) continue;
            [, $col, $ref, $refCol] = $m;
            $orphelins = "FROM {$fk->tbl} t WHERE t.\"$col\" IS NOT NULL
                          AND NOT EXISTS (SELECT 1 FROM \"$ref\" r WHERE r.\"$refCol\" = t.\"$col\")";
            $nb = $pg->selectOne("SELECT count(*) AS n $orphelins")->n;
            if (!$nb) continue;
            $nullable = $pg->selectOne("SELECT is_nullable FROM information_schema.columns
                WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?", [trim($fk->tbl, '"'), $col])->is_nullable;
            if ($nullable !== 'YES') {
                throw new \RuntimeException("$nb ligne(s) de {$fk->tbl}.$col pointent vers un $ref inexistant (colonne obligatoire) : à corriger dans MySQL.");
            }
            $pg->update("UPDATE {$fk->tbl} SET \"$col\" = NULL WHERE \"$col\" IS NOT NULL
                         AND NOT EXISTS (SELECT 1 FROM \"$ref\" r WHERE r.\"$refCol\" = {$fk->tbl}.\"$col\")");
            $this->warn("  $nb ligne(s) de {$fk->tbl}.$col pointaient vers un $ref inexistant : mises à NULL.");
        }
    }

    private function convertir($v, string $type)
    {
        if ($v === null) return null;
        switch ($type) {
            case 'boolean':
                return (bool) $v;
            case 'date':
            case 'timestamp without time zone':
                // MySQL tolère les dates nulles « 0000-00-00 »
                return str_starts_with((string) $v, '0000-00-00') ? null : $v;
            case 'text':
            case 'character varying':
            case 'json':
                // PostgreSQL refuse l'octet nul dans les chaînes
                return is_string($v) ? str_replace("\0", '', $v) : $v;
        }
        return $v;
    }
}
