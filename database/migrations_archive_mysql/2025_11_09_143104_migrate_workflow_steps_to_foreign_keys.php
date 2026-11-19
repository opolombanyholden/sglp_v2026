<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION : Workflow Steps - ENUM → Foreign Keys
 * 
 * Remplace les colonnes ENUM type_organisation et type_operation 
 * par des Foreign Keys vers organisation_types et operation_types
 * 
 * Phase 1 : Ajout des FK et migration des données
 * (Les colonnes ENUM seront supprimées dans une migration ultérieure après validation)
 * 
 * Projet: SGLP
 * Date: 2025-11-09
 */
return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            // ====================================
            // ÉTAPE 1 : Ajouter les nouvelles colonnes FK
            // ====================================
            
            // Organisation Type FK (nullable temporairement pour la migration)
            $table->unsignedBigInteger('organisation_type_id')
                ->nullable()
                ->after('description')
                ->comment('FK vers organisation_types (remplace ENUM type_organisation)');
            
            // Operation Type FK (nullable temporairement pour la migration)
            $table->unsignedBigInteger('operation_type_id')
                ->nullable()
                ->after('organisation_type_id')
                ->comment('FK vers operation_types (remplace ENUM type_operation)');
            
            // Index pour performances
            $table->index(['organisation_type_id', 'operation_type_id', 'is_active'], 
                'idx_workflow_org_op_active');
        });

        // ====================================
        // ÉTAPE 2 : Migrer les données ENUM → FK
        // ====================================
        
        // Mapping des codes ENUM vers les IDs
        $this->migrateOrganisationTypes();
        $this->migrateOperationTypes();
        
        // ====================================
        // ÉTAPE 3 : Rendre les colonnes NOT NULL
        // ====================================
        
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('organisation_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('operation_type_id')->nullable(false)->change();
        });
        
        // ====================================
        // ÉTAPE 4 : Ajouter les contraintes FK
        // ====================================
        
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->foreign('organisation_type_id', 'fk_workflow_steps_org_type')
                ->references('id')
                ->on('organisation_types')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
            $table->foreign('operation_type_id', 'fk_workflow_steps_op_type')
                ->references('id')
                ->on('operation_types')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
        
        // ====================================
        // ÉTAPE 5 : Renommer les colonnes ENUM (backup)
        // ====================================
        
        // On garde les ENUM pour rétrocompatibilité temporaire
        // Elles seront supprimées dans une migration ultérieure
        DB::statement('ALTER TABLE workflow_steps 
            CHANGE COLUMN type_organisation type_organisation_old 
            ENUM("association","ong","parti_politique","confession_religieuse") 
            COMMENT "DEPRECATED - Utiliser organisation_type_id"');
        
        DB::statement('ALTER TABLE workflow_steps 
            CHANGE COLUMN type_operation type_operation_old 
            ENUM("creation","modification","cessation","ajout_adherent","retrait_adherent","declaration_activite","changement_statutaire") 
            COMMENT "DEPRECATED - Utiliser operation_type_id"');
    }

    /**
     * Migrer les types d'organisation ENUM → FK
     */
    protected function migrateOrganisationTypes(): void
    {
        // Récupérer tous les workflow_steps
        $steps = DB::table('workflow_steps')->get();
        
        foreach ($steps as $step) {
            // Trouver l'ID correspondant au code ENUM
            $orgTypeId = DB::table('organisation_types')
                ->where('code', $step->type_organisation)
                ->value('id');
            
            if ($orgTypeId) {
                DB::table('workflow_steps')
                    ->where('id', $step->id)
                    ->update(['organisation_type_id' => $orgTypeId]);
            } else {
                // Log si mapping échoue
                \Log::warning("Migration workflow_steps: Organisation type non trouvé", [
                    'step_id' => $step->id,
                    'type_organisation' => $step->type_organisation
                ]);
            }
        }
    }

    /**
     * Migrer les types d'opération ENUM → FK
     */
    protected function migrateOperationTypes(): void
    {
        // Récupérer tous les workflow_steps
        $steps = DB::table('workflow_steps')->get();
        
        foreach ($steps as $step) {
            // Trouver l'ID correspondant au code ENUM
            $opTypeId = DB::table('operation_types')
                ->where('code', $step->type_operation)
                ->value('id');
            
            if ($opTypeId) {
                DB::table('workflow_steps')
                    ->where('id', $step->id)
                    ->update(['operation_type_id' => $opTypeId]);
            } else {
                // Log si mapping échoue
                \Log::warning("Migration workflow_steps: Operation type non trouvé", [
                    'step_id' => $step->id,
                    'type_operation' => $step->type_operation
                ]);
            }
        }
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            // Supprimer les contraintes FK
            $table->dropForeign('fk_workflow_steps_org_type');
            $table->dropForeign('fk_workflow_steps_op_type');
            
            // Supprimer l'index
            $table->dropIndex('idx_workflow_org_op_active');
            
            // Supprimer les colonnes FK
            $table->dropColumn(['organisation_type_id', 'operation_type_id']);
        });
        
        // Restaurer les noms originaux des colonnes ENUM
        DB::statement('ALTER TABLE workflow_steps 
            CHANGE COLUMN type_organisation_old type_organisation 
            ENUM("association","ong","parti_politique","confession_religieuse")');
        
        DB::statement('ALTER TABLE workflow_steps 
            CHANGE COLUMN type_operation_old type_operation 
            ENUM("creation","modification","cessation","ajout_adherent","retrait_adherent","declaration_activite","changement_statutaire")');
    }
};