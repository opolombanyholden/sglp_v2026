<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Le prénom devient facultatif dans les formulaires (déclarant, fondateurs,
 * adhérents, membres du bureau, comptes utilisateurs).
 *
 * Ces trois tables refusaient NULL : sans cette migration, une saisie sans
 * prénom échouerait avec « Column 'prenom' cannot be null ».
 *
 * users.prenom et fondateurs.prenom acceptent déjà NULL.
 */
return new class extends Migration {
    /**
     * Longueur d'origine de chaque colonne, à préserver.
     */
    private array $colonnes = [
        'adherents' => 255,
        'membres_bureau' => 100,
        'organe_members' => 255,
    ];

    public function up(): void
    {
        foreach ($this->colonnes as $table => $longueur) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'prenom')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($longueur) {
                $blueprint->string('prenom', $longueur)
                    ->charset('utf8mb4')
                    ->collation('utf8mb4_unicode_ci')
                    ->nullable()
                    ->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->colonnes as $table => $longueur) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'prenom')) {
                continue;
            }

            // NOT NULL refuserait les lignes créées sans prénom depuis le up()
            DB::table($table)->whereNull('prenom')->update(['prenom' => '']);

            Schema::table($table, function (Blueprint $blueprint) use ($longueur) {
                $blueprint->string('prenom', $longueur)
                    ->charset('utf8mb4')
                    ->collation('utf8mb4_unicode_ci')
                    ->nullable(false)
                    ->change();
            });
        }
    }
};
