<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ValidationEntitySeeder extends Seeder
{
    /**
     * Exécuter les seeds.
     */
    public function run(): void
    {
        // Désactiver les vérifications de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider la table
        DB::table('validation_entities')->truncate();
        
        // Réactiver les vérifications
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $entities = [
            [
                'code' => 'DIR_INTERIEUR',
                'nom' => 'Direction de l\'Intérieur',
                'description' => 'Direction générale responsable de la supervision des organisations',
                'type' => 'direction',
                'email_notification' => 'direction@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 50,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SRV_LIBERTES',
                'nom' => 'Service des Libertés Publiques',
                'description' => 'Service en charge de la gestion des libertés publiques et associations',
                'type' => 'service',
                'email_notification' => 'libertes.publiques@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 30,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SRV_JURIDIQUE',
                'nom' => 'Service Juridique',
                'description' => 'Service de vérification juridique des statuts et documents',
                'type' => 'service',
                'email_notification' => 'juridique@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 25,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'COMMISSION_TECH',
                'nom' => 'Commission Technique',
                'description' => 'Commission d\'évaluation technique des dossiers',
                'type' => 'commission',
                'email_notification' => 'commission.technique@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 20,
                'horaires_travail' => json_encode([
                    'lundi' => ['09:00', '16:00'],
                    'jeudi' => ['09:00', '16:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'DEP_EXTERNE',
                'nom' => 'Département Externe',
                'description' => 'Département de coordination avec les partenaires externes',
                'type' => 'departement',
                'email_notification' => 'externe@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 15,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SRV_ARCHIVES',
                'nom' => 'Service des Archives',
                'description' => 'Service de gestion et archivage des dossiers validés',
                'type' => 'service',
                'email_notification' => 'archives@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 60,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'SRV_NOTIFICATION',
                'nom' => 'Service Notification',
                'description' => 'Service de notification et communication avec les opérateurs',
                'type' => 'service',
                'email_notification' => 'notifications@interieur.gouv.ga',
                'is_active' => 1,
                'capacite_traitement' => 100,
                'horaires_travail' => json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Insertion directe
        DB::table('validation_entities')->insert($entities);

        $this->command->info('✅ ' . count($entities) . ' entités de validation créées avec succès');
    }
}