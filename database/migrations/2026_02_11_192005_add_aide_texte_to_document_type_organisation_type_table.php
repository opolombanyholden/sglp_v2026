<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajout de la colonne aide_texte à la table pivot document_type_organisation_type
 *
 * Correction : La colonne aide_texte était référencée dans le contrôleur
 * OrganisationTypeController (store/update) et dans les vues (edit/create)
 * mais était absente de cette table pivot.
 *
 * Projet : SGLP
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('document_type_organisation_type', function (Blueprint $table) {
            if (!Schema::hasColumn('document_type_organisation_type', 'aide_texte')) {
                $table->text('aide_texte')->nullable()->after('ordre')
                    ->comment('Instructions ou aide spécifique pour ce document et ce type d\'organisation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_type_organisation_type', function (Blueprint $table) {
            if (Schema::hasColumn('document_type_organisation_type', 'aide_texte')) {
                $table->dropColumn('aide_texte');
            }
        });
    }
};
