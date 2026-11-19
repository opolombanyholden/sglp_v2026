<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige l'erreur 1451 « Cannot delete or update a parent row » survenue en
 * production lors de la soumission d'un dossier.
 *
 * Plusieurs flux suppriment les fondateurs d'une organisation pour les recréer
 * (soumission opérateur, reprise d'un brouillon, mise à jour admin, résolution
 * d'un conflit de dénomination). La contrainte `adherents.fondateur_id` était
 * en ON DELETE RESTRICT : dès qu'un adhérent référençait un fondateur, MySQL
 * rejetait la suppression et toute la soumission échouait.
 *
 * `fondateur_id` est nullable et toutes les autres clés étrangères nullables de
 * la table `adherents` sont déjà en ON DELETE SET NULL — celle-ci faisait
 * exception. On l'aligne : l'adhérent est conservé, seule la référence devenue
 * obsolète est remise à NULL.
 *
 * Corriger la contrainte plutôt que l'ordre des suppressions dans le code
 * protège aussi les flux futurs, et évite d'avoir à supprimer les adhérents
 * (perte de données) pour pouvoir remplacer les fondateurs.
 */
return new class extends Migration {
    private const TABLE = 'adherents';
    private const COLONNE = 'fondateur_id';
    private const NOM_PAR_DEFAUT = 'adherents_fondateur_id_fk';

    /**
     * Retrouve le nom réel de la contrainte : il diffère selon que la base a
     * été construite par les migrations ou restaurée depuis un dump.
     */
    private function nomContrainte(): ?string
    {
        $ligne = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
               AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [self::TABLE, self::COLONNE, 'fondateurs']
        );

        return $ligne->CONSTRAINT_NAME ?? null;
    }

    private function recreer(string $regleSuppression): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasColumn(self::TABLE, self::COLONNE)) {
            return;
        }

        $existante = $this->nomContrainte();
        $nom = $existante ?? self::NOM_PAR_DEFAUT;

        if ($existante) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` DROP FOREIGN KEY `%s`',
                self::TABLE,
                $existante
            ));
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`)
             REFERENCES `fondateurs` (`id`) ON DELETE %s ON UPDATE RESTRICT',
            self::TABLE,
            $nom,
            self::COLONNE,
            $regleSuppression
        ));
    }

    public function up(): void
    {
        $this->recreer('SET NULL');
    }

    public function down(): void
    {
        // Retour à RESTRICT : les suppressions de fondateurs redeviendront
        // bloquantes dès qu'un adhérent les référence.
        $this->recreer('RESTRICT');
    }
};
