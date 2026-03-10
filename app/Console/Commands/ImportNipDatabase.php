<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportNipDatabase extends Command
{
    protected $signature = 'nip:import
                            {--file= : Chemin vers le fichier CSV (défaut: conception/BD_NIP_FINALE.csv)}
                            {--chunk=500 : Nombre de lignes par lot INSERT}
                            {--fresh : Vider la table avant l\'import}';

    protected $description = 'Importe les NIP gabonais depuis BD_NIP_FINALE.csv dans la table nip_database';

    public function handle(): int
    {
        $file = $this->option('file') ?: base_path('conception/BD_NIP_FINALE.csv');
        $chunkSize = (int) $this->option('chunk');

        if (!file_exists($file)) {
            $this->error("Fichier introuvable : {$file}");
            return 1;
        }

        if ($this->option('fresh')) {
            $this->warn('Vidage de la table nip_database...');
            DB::table('nip_database')->truncate();
        }

        $totalLines = $this->countLines($file);
        $this->info("Fichier : {$file}");
        $this->info(number_format($totalLines - 1, 0, ',', ' ') . " enregistrements à importer (taille des lots : {$chunkSize})");

        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error('Impossible d\'ouvrir le fichier.');
            return 1;
        }

        // Sauter la ligne d'en-tête
        fgets($handle);

        $now        = now()->toDateTimeString();
        $batch      = [];
        $imported   = 0;
        $skipped    = 0;
        $lineNum    = 1;

        $bar = $this->output->createProgressBar($totalLines - 1);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — importés: %imported% | ignorés: %skipped%');
        $bar->setMessage('0', 'imported');
        $bar->setMessage('0', 'skipped');
        $bar->start();

        while (($raw = fgets($handle)) !== false) {
            $lineNum++;
            $row = $this->parseLine($raw);

            if ($row === null) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $batch[] = [
                'nom'           => $row['nom'],
                'prenom'        => $row['prenom'],
                'nip'           => $row['nip'],
                'date_naissance'=> $row['date_naissance'],
                'lieu_naissance'=> $row['lieu_naissance'],
                'statut'        => 'actif',
                'source_import' => 'BD_NIP_FINALE.csv',
                'date_import'   => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($batch) >= $chunkSize) {
                [$ins, $skip] = $this->insertBatch($batch);
                $imported += $ins;
                $skipped  += $skip;
                $batch = [];
                $bar->setMessage(number_format($imported, 0, ',', ' '), 'imported');
                $bar->setMessage(number_format($skipped,  0, ',', ' '), 'skipped');
            }

            $bar->advance();
        }

        // Dernier lot
        if (!empty($batch)) {
            [$ins, $skip] = $this->insertBatch($batch);
            $imported += $ins;
            $skipped  += $skip;
        }

        $bar->setMessage(number_format($imported, 0, ',', ' '), 'imported');
        $bar->setMessage(number_format($skipped,  0, ',', ' '), 'skipped');
        $bar->finish();

        fclose($handle);

        $this->newLine(2);
        $this->info('Import terminé.');
        $this->table(
            ['Résultat', 'Nombre'],
            [
                ['Importés',   number_format($imported, 0, ',', ' ')],
                ['Ignorés (doublons / invalides)', number_format($skipped,  0, ',', ' ')],
                ['Total traités', number_format($imported + $skipped, 0, ',', ' ')],
            ]
        );

        return 0;
    }

    // -------------------------------------------------------------------------
    // Parsing d'une ligne du CSV
    // Format : "LASTNAME,""FIRSTNAME"",""UIN"",""DATE_OF_BIRTH"",""PLACE_OF_BIRTH""";
    // -------------------------------------------------------------------------
    private function parseLine(string $raw): ?array
    {
        // Supprimer BOM éventuel, espaces et fins de ligne Windows/Unix
        $raw = ltrim($raw, "\xEF\xBB\xBF");
        $raw = rtrim($raw, "\r\n ");

        // Retirer le ; final et les guillemets externes
        $raw = rtrim($raw, ';');
        if (str_starts_with($raw, '"') && str_ends_with($raw, '"')) {
            $raw = substr($raw, 1, -1);
        }

        // Découper par , puis nettoyer les "" internes de chaque champ
        $parts = explode(',', $raw);
        $fields = array_map(fn($f) => trim($f, '"'), $parts);

        if (count($fields) < 5) {
            return null;
        }

        $nom            = strtoupper(trim($fields[0]));
        $prenom         = ucwords(strtolower(trim($fields[1])));
        $nip            = trim($fields[2]);
        $dateNaissance  = trim($fields[3]);
        $lieuNaissance  = strtoupper(trim($fields[4]));

        // Valider les champs obligatoires
        if (empty($nom) || empty($nip)) {
            return null;
        }

        // Valider le format NIP (ex: 03-0003-19730306 ou 5F-0192-19570101)
        if (!preg_match('/^[A-Z0-9]{2}-\d{4}-\d{8}$/', $nip)) {
            return null;
        }

        // Valider la date (format YYYY-MM-DD)
        if (!empty($dateNaissance) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateNaissance)) {
            $dateNaissance = null;
        }

        return [
            'nom'            => $nom,
            'prenom'         => $prenom ?: null,
            'nip'            => $nip,
            'date_naissance' => $dateNaissance ?: null,
            'lieu_naissance' => $lieuNaissance ?: null,
        ];
    }

    // -------------------------------------------------------------------------
    // INSERT par lot avec ignorance des doublons sur le NIP unique
    // -------------------------------------------------------------------------
    private function insertBatch(array $batch): array
    {
        try {
            // INSERT IGNORE : skip les NIP déjà existants (contrainte UNIQUE)
            $placeholders = implode(',', array_fill(0, count($batch), '(?,?,?,?,?,?,?,?,?,?)'));
            $values = [];
            foreach ($batch as $row) {
                array_push(
                    $values,
                    $row['nom'],
                    $row['prenom'],
                    $row['nip'],
                    $row['date_naissance'],
                    $row['lieu_naissance'],
                    $row['statut'],
                    $row['source_import'],
                    $row['date_import'],
                    $row['created_at'],
                    $row['updated_at'],
                );
            }

            $affected = DB::affectingStatement(
                "INSERT IGNORE INTO nip_database
                    (nom, prenom, nip, date_naissance, lieu_naissance, statut, source_import, date_import, created_at, updated_at)
                 VALUES {$placeholders}",
                $values
            );

            $inserted = $affected;
            $ignored  = count($batch) - $inserted;
            return [$inserted, $ignored];

        } catch (\Exception $e) {
            // En cas d'erreur sur un lot, on tente ligne par ligne pour ne pas tout perdre
            $inserted = 0;
            $ignored  = 0;
            foreach ($batch as $row) {
                try {
                    DB::table('nip_database')->insertOrIgnore([
                        'nom'            => $row['nom'],
                        'prenom'         => $row['prenom'],
                        'nip'            => $row['nip'],
                        'date_naissance' => $row['date_naissance'],
                        'lieu_naissance' => $row['lieu_naissance'],
                        'statut'         => $row['statut'],
                        'source_import'  => $row['source_import'],
                        'date_import'    => $row['date_import'],
                        'created_at'     => $row['created_at'],
                        'updated_at'     => $row['updated_at'],
                    ]);
                    $inserted++;
                } catch (\Exception) {
                    $ignored++;
                }
            }
            return [$inserted, $ignored];
        }
    }

    private function countLines(string $file): int
    {
        $count = 0;
        $handle = fopen($file, 'r');
        while (!feof($handle)) {
            fgets($handle);
            $count++;
        }
        fclose($handle);
        return $count;
    }
}
