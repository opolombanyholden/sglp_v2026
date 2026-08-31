<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les champs téléphone acceptent désormais une saisie libre pouvant contenir
 * plusieurs numéros (« 077 12 34 56 / 066 98 76 54 »).
 *
 * Ces deux colonnes étaient trop courtes pour ce format : la validation serveur
 * autorise 255 caractères, mais MySQL en mode strict aurait rejeté la valeur
 * (erreur 1406, « Data too long »).
 *
 * Les autres colonnes téléphone sont déjà en varchar(255).
 * `nip_database.telephone` reste volontairement en varchar(20) : c'est une
 * référence d'identité importée (un citoyen, un numéro), et l'élargir imposerait
 * une reconstruction de table d'environ 1 million de lignes en production.
 */
return new class extends Migration {
    /**
     * table => [colonne, longueur d'origine, nouvelle longueur]
     */
    private array $colonnes = [
        'users' => ['phone', 20, 255],
        'membres_bureau' => ['contact', 100, 255],
    ];

    public function up(): void
    {
        foreach ($this->colonnes as $table => [$colonne, $avant, $apres]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $colonne)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($colonne, $apres) {
                $blueprint->string($colonne, $apres)
                    ->charset('utf8mb4')
                    ->collation('utf8mb4_unicode_ci')
                    ->nullable()
                    ->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->colonnes as $table => [$colonne, $avant, $apres]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $colonne)) {
                continue;
            }

            // ⚠️ Rétrécir la colonne tronque les saisies multi-numéros créées
            // depuis le up(). On tronque explicitement pour que le rollback
            // n'échoue pas en mode strict, en acceptant cette perte.
            DB::table($table)
                ->whereRaw("CHAR_LENGTH(`{$colonne}`) > ?", [$avant])
                ->update([$colonne => DB::raw("LEFT(`{$colonne}`, {$avant})")]);

            Schema::table($table, function (Blueprint $blueprint) use ($colonne, $avant) {
                $blueprint->string($colonne, $avant)
                    ->charset('utf8mb4')
                    ->collation('utf8mb4_unicode_ci')
                    ->nullable()
                    ->change();
            });
        }
    }
};
