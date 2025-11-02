<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowStepSeeder extends Seeder
{
    /**
     * Exécuter les seeds.
     */
    public function run(): void
    {
        // Désactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider la table
        DB::table('workflow_steps')->truncate();
        
        // Réactiver les vérifications
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ========================================
        // WORKFLOW : ASSOCIATION - CRÉATION
        // ========================================
        
        $steps = [
            [
                'code' => 'ASSO_CREATE_STEP1',
                'libelle' => 'Réception et Enregistrement',
                'description' => 'Réception du dossier et enregistrement dans le système',
                'type_organisation' => 'association',
                'type_operation' => 'creation',
                'numero_passage' => 1,
                'is_active' => 1,
                'permet_rejet' => 0,
                'permet_commentaire' => 1,
                'genere_document' => 1,
                'template_document' => 'accuse_reception',
                'champs_requis' => json_encode([
                    'denomination',
                    'objet',
                    'siege_social',
                    'membres_fondateurs'
                ]),
                'delai_traitement' => 24,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'ASSO_CREATE_STEP2',
                'libelle' => 'Vérification des Documents',
                'description' => 'Vérification de la complétude et conformité des documents',
                'type_organisation' => 'association',
                'type_operation' => 'creation',
                'numero_passage' => 2,
                'is_active' => 1,
                'permet_rejet' => 1,
                'permet_commentaire' => 1,
                'genere_document' => 0,
                'template_document' => null,
                'champs_requis' => json_encode([
                    'statuts',
                    'reglement_interieur',
                    'liste_membres'
                ]),
                'delai_traitement' => 48,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'ASSO_CREATE_STEP3',
                'libelle' => 'Examen Juridique',
                'description' => 'Examen juridique des statuts et conformité légale',
                'type_organisation' => 'association',
                'type_operation' => 'creation',
                'numero_passage' => 3,
                'is_active' => 1,
                'permet_rejet' => 1,
                'permet_commentaire' => 1,
                'genere_document' => 1,
                'template_document' => 'avis_juridique',
                'champs_requis' => json_encode([
                    'statuts',
                    'objet_social'
                ]),
                'delai_traitement' => 72,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'ASSO_CREATE_STEP4',
                'libelle' => 'Validation Commission Technique',
                'description' => 'Présentation et validation par la commission technique',
                'type_organisation' => 'association',
                'type_operation' => 'creation',
                'numero_passage' => 4,
                'is_active' => 1,
                'permet_rejet' => 1,
                'permet_commentaire' => 1,
                'genere_document' => 1,
                'template_document' => 'pv_commission',
                'champs_requis' => json_encode([]),
                'delai_traitement' => 72,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'ASSO_CREATE_STEP5',
                'libelle' => 'Approbation et Signature',
                'description' => 'Approbation finale et signature du récépissé',
                'type_organisation' => 'association',
                'type_operation' => 'creation',
                'numero_passage' => 5,
                'is_active' => 1,
                'permet_rejet' => 0,
                'permet_commentaire' => 1,
                'genere_document' => 1,
                'template_document' => 'recepisse_declaration',
                'champs_requis' => json_encode([]),
                'delai_traitement' => 48,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insertion directe
        DB::table('workflow_steps')->insert($steps);

        $this->command->info('✅ ' . count($steps) . ' étapes de workflow créées avec succès');
        $this->command->info('📋 Workflow "Association - Création" : 5 étapes');
    }
}