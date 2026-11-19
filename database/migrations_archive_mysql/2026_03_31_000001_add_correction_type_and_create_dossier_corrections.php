<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter 'correction' à l'enum type_operation des dossiers
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN type_operation ENUM('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire','correction') NOT NULL");

        // 2. Ajouter 'correction' à l'enum type_operation des dossier_operations
        DB::statement("ALTER TABLE dossier_operations MODIFY COLUMN type_operation VARCHAR(50) NOT NULL");

        // 3. Créer la table dossier_corrections
        Schema::create('dossier_corrections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dossier_id')->comment('Dossier de correction (nouvelle version)');
            $table->unsignedBigInteger('original_dossier_id')->comment('Dossier approuvé corrigé');
            $table->string('champ_corrige')->comment('Nom du champ corrigé');
            $table->enum('categorie', ['organisation', 'adherent', 'fondateur', 'membre_bureau', 'document', 'autre'])->default('organisation');
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();
            $table->text('motif_correction')->comment('Justification obligatoire');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID adhérent/fondateur/membre si applicable');
            $table->unsignedBigInteger('corrected_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('dossier_id')->references('id')->on('dossiers')->onDelete('cascade');
            $table->foreign('original_dossier_id')->references('id')->on('dossiers')->onDelete('cascade');
            $table->foreign('corrected_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['dossier_id', 'categorie']);
            $table->index('original_dossier_id');
        });

        // 4. Ajouter le type d'opération 'correction' dans operation_types
        DB::table('operation_types')->insert([
            'code' => 'correction',
            'libelle' => 'Correction administrative',
            'description' => 'Correction d\'erreurs sur un dossier approuvé',
            'is_active' => true,
            'ordre' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dossier_corrections');
        DB::table('operation_types')->where('code', 'correction')->delete();
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN type_operation ENUM('creation','modification','cessation','ajout_adherent','retrait_adherent','declaration_activite','changement_statutaire') NOT NULL");
    }
};
