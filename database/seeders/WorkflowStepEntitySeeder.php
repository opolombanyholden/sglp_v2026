<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkflowStepEntitySeeder extends Seeder
{
    /**
     * Exécuter les seeds.
     */
    public function run(): void
    {
        // Désactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider la table
        DB::table('workflow_step_entities')->truncate();
        
        // Réactiver les vérifications
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Récupérer les IDs des étapes et entités par leur code
        $steps = [
            'step1' => DB::table('workflow_steps')->where('code', 'ASSO_CREATE_STEP1')->value('id'),
            'step2' => DB::table('workflow_steps')->where('code', 'ASSO_CREATE_STEP2')->value('id'),
            'step3' => DB::table('workflow_steps')->where('code', 'ASSO_CREATE_STEP3')->value('id'),
            'step4' => DB::table('workflow_steps')->where('code', 'ASSO_CREATE_STEP4')->value('id'),
            'step5' => DB::table('workflow_steps')->where('code', 'ASSO_CREATE_STEP5')->value('id'),
        ];

        $entities = [
            'dir_interieur' => DB::table('validation_entities')->where('code', 'DIR_INTERIEUR')->value('id'),
            'srv_libertes' => DB::table('validation_entities')->where('code', 'SRV_LIBERTES')->value('id'),
            'srv_juridique' => DB::table('validation_entities')->where('code', 'SRV_JURIDIQUE')->value('id'),
            'commission_tech' => DB::table('validation_entities')->where('code', 'COMMISSION_TECH')->value('id'),
        ];

        $links = [];

        // ÉTAPE 1 : Réception et Enregistrement → Service Libertés Publiques
        if ($steps['step1'] && $entities['srv_libertes']) {
            $links[] = [
                'workflow_step_id' => $steps['step1'],
                'validation_entity_id' => $entities['srv_libertes'],
                'ordre' => 1,
                'is_optional' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // ÉTAPE 2 : Vérification des Documents → Service Libertés Publiques
        if ($steps['step2'] && $entities['srv_libertes']) {
            $links[] = [
                'workflow_step_id' => $steps['step2'],
                'validation_entity_id' => $entities['srv_libertes'],
                'ordre' => 1,
                'is_optional' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // ÉTAPE 3 : Examen Juridique → Service Juridique
        if ($steps['step3'] && $entities['srv_juridique']) {
            $links[] = [
                'workflow_step_id' => $steps['step3'],
                'validation_entity_id' => $entities['srv_juridique'],
                'ordre' => 1,
                'is_optional' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // ÉTAPE 4 : Validation Commission → Commission Technique
        if ($steps['step4'] && $entities['commission_tech']) {
            $links[] = [
                'workflow_step_id' => $steps['step4'],
                'validation_entity_id' => $entities['commission_tech'],
                'ordre' => 1,
                'is_optional' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // ÉTAPE 5 : Approbation et Signature → Direction de l'Intérieur
        if ($steps['step5'] && $entities['dir_interieur']) {
            $links[] = [
                'workflow_step_id' => $steps['step5'],
                'validation_entity_id' => $entities['dir_interieur'],
                'ordre' => 1,
                'is_optional' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insertion directe
        if (!empty($links)) {
            DB::table('workflow_step_entities')->insert($links);
        }

        $this->command->info('✅ ' . count($links) . ' liens étape-entité créés avec succès');
        $this->command->info('   • STEP1 → Service Libertés Publiques');
        $this->command->info('   • STEP2 → Service Libertés Publiques');
        $this->command->info('   • STEP3 → Service Juridique');
        $this->command->info('   • STEP4 → Commission Technique');
        $this->command->info('   • STEP5 → Direction de l\'Intérieur');
    }
}