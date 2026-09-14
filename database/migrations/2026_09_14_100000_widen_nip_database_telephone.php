<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dernière colonne téléphone encore trop étroite.
 *
 * Les champs de saisie acceptent désormais une chaîne libre pouvant contenir
 * plusieurs numéros. `nip_database.telephone` restait en varchar(20) : une
 * saisie plus longue aurait été rejetée par MySQL en mode strict (erreur 1406),
 * alors même que la validation serveur autorise 255 caractères.
 *
 * ⚠️ EN PRODUCTION : cette table compte environ 1 000 000 de lignes. Élargir un
 * varchar de 20 à 255 fait passer le préfixe de longueur de 1 à 2 octets, ce qui
 * impose à MySQL une reconstruction complète de la table — comptez plusieurs
 * dizaines de secondes, pendant lesquelles la table est verrouillée en écriture.
 * À lancer hors heures de service, après sauvegarde.
 */
return new class extends Migration {
    private const TABLE = 'nip_database';
    private const COLONNE = 'telephone';

    public function up(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasColumn(self::TABLE, self::COLONNE)) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::COLONNE, 255)
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable(self::TABLE) || !Schema::hasColumn(self::TABLE, self::COLONNE)) {
            return;
        }

        // Rétrécir tronque les saisies multi-numéros créées depuis le up().
        // On tronque explicitement pour que le rollback n'échoue pas en mode strict.
        DB::table(self::TABLE)
            ->whereRaw('CHAR_LENGTH(`' . self::COLONNE . '`) > 20')
            ->update([self::COLONNE => DB::raw('LEFT(`' . self::COLONNE . '`, 20)')]);

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->string(self::COLONNE, 20)
                ->charset('utf8mb4')
                ->collation('utf8mb4_unicode_ci')
                ->nullable()
                ->change();
        });
    }
};
