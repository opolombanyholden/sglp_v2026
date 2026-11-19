<?php
/**
 * PERMISSION SEEDER - PNGDI
 * Initialisation des permissions système pour PNGDI
 * Compatible PHP 7.3.29 - Laravel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('🚀 Création des permissions système PNGDI...');
        
        $permissions = Permission::getSystemPermissions();
        $created = 0;
        $updated = 0;
        
        foreach ($permissions as $category => $categoryPermissions) {
            $this->command->info("📂 Catégorie: " . ucfirst($category));
            
            foreach ($categoryPermissions as $name => $displayName) {
                $permission = Permission::firstOrCreate(
                    ['name' => $name],
                    [
                        'display_name' => $displayName,
                        'category' => $category,
                        'description' => $this->generateDescription($name, $displayName, $category)
                    ]
                );
                
                if ($permission->wasRecentlyCreated) {
                    $created++;
                    $this->command->line("  ✅ Créé: {$name}");
                } else {
                    // Mettre à jour si nécessaire
                    $permission->update([
                        'display_name' => $displayName,
                        'category' => $category,
                        'description' => $this->generateDescription($name, $displayName, $category)
                    ]);
                    $updated++;
                    $this->command->line("  🔄 Mis à jour: {$name}");
                }
            }
        }
        
        $this->command->info('');
        $this->command->info("✅ Permissions créées: {$created}");
        $this->command->info("🔄 Permissions mises à jour: {$updated}");
        $this->command->info('📊 Total permissions système: ' . Permission::count());
        
        // Vérifier la cohérence
        $this->validatePermissions();
        
        $this->command->info('🎉 Permissions système PNGDI initialisées avec succès !');
    }
    
    /**
     * Générer une description détaillée pour une permission
     */
    private function generateDescription($name, $displayName, $category)
    {
        $descriptions = [
            // Gestion Utilisateurs
            'users.view' => 'Permet de consulter la liste des utilisateurs et leurs informations de base',
            'users.create' => 'Permet de créer de nouveaux comptes utilisateurs dans le système',
            'users.edit' => 'Permet de modifier les informations des utilisateurs existants',
            'users.delete' => 'Permet de supprimer définitivement des comptes utilisateurs',
            'users.export' => 'Permet d\'exporter les données utilisateurs vers Excel, PDF ou CSV',
            'users.import' => 'Permet d\'importer des utilisateurs en masse depuis des fichiers',
            'users.roles' => 'Permet de gérer les rôles et attributions des utilisateurs',
            'users.permissions' => 'Permet de gérer les permissions spécifiques des utilisateurs',
            'users.sessions' => 'Permet de consulter et gérer les sessions actives des utilisateurs',
            'users.verify' => 'Permet de vérifier et valider les comptes utilisateurs',
            
            // Gestion Organisations
            'orgs.view' => 'Permet de consulter les organisations enregistrées dans le système',
            'orgs.create' => 'Permet de créer de nouvelles organisations (associations, partis, etc.)',
            'orgs.edit' => 'Permet de modifier les informations des organisations existantes',
            'orgs.delete' => 'Permet de supprimer définitivement des organisations',
            'orgs.validate' => 'Permet de valider les demandes d\'enregistrement d\'organisations',
            'orgs.reject' => 'Permet de rejeter les demandes d\'enregistrement avec motifs',
            'orgs.archive' => 'Permet d\'archiver les organisations inactives ou radiées',
            'orgs.export' => 'Permet d\'exporter les données des organisations',
            'orgs.suspend' => 'Permet de suspendre temporairement une organisation',
            'orgs.reactivate' => 'Permet de réactiver une organisation suspendue',
            'orgs.manage_adherents' => 'Permet de gérer les adhérents des organisations',
            'orgs.manage_documents' => 'Permet de gérer les documents des organisations',
            
            // Gestion Workflow
            'workflow.view' => 'Permet de consulter l\'état du workflow et des dossiers',
            'workflow.assign' => 'Permet d\'assigner des dossiers à des agents pour traitement',
            'workflow.validate' => 'Permet de valider les étapes du processus de traitement',
            'workflow.reject' => 'Permet de rejeter des demandes avec justification',
            'workflow.reports' => 'Permet de générer des rapports sur l\'activité du workflow',
            'workflow.lock' => 'Permet de verrouiller des dossiers pour traitement exclusif',
            'workflow.unlock' => 'Permet de déverrouiller des dossiers bloqués',
            'workflow.comment' => 'Permet d\'ajouter des commentaires aux dossiers',
            'workflow.history' => 'Permet de consulter l\'historique complet des dossiers',
            'workflow.priority' => 'Permet de modifier les priorités de traitement',
            
            // Gestion Système
            'system.config' => 'Permet de modifier la configuration générale du système',
            'system.backup' => 'Permet de créer et restaurer des sauvegardes système',
            'system.logs' => 'Permet de consulter les journaux d\'activité du système',
            'system.reports' => 'Permet de générer des rapports système et statistiques',
            'system.maintenance' => 'Permet d\'activer le mode maintenance du système',
            'system.updates' => 'Permet de gérer les mises à jour du système',
            'system.monitoring' => 'Permet d\'accéder aux outils de monitoring et surveillance',
            'system.security' => 'Permet de gérer les paramètres de sécurité avancés',
            'system.integrations' => 'Permet de configurer les intégrations avec d\'autres systèmes',
            'system.notifications' => 'Permet de configurer les notifications système',
            
            // Gestion Contenus
            'content.view' => 'Permet de consulter tous les contenus du système',
            'content.create' => 'Permet de créer de nouveaux contenus et articles',
            'content.edit' => 'Permet de modifier les contenus existants',
            'content.delete' => 'Permet de supprimer des contenus',
            'content.publish' => 'Permet de publier des contenus pour les rendre visibles',
            'content.moderate' => 'Permet de modérer et valider les contenus soumis',
            'content.media' => 'Permet de gérer les fichiers médias (images, documents)',
            'content.templates' => 'Permet de gérer les modèles de documents',
            
            // Gestion Rapports
            'reports.view' => 'Permet de consulter tous les rapports disponibles',
            'reports.create' => 'Permet de créer des rapports personnalisés',
            'reports.export' => 'Permet d\'exporter les rapports dans différents formats',
            'reports.schedule' => 'Permet de programmer des rapports automatiques',
            'reports.analytics' => 'Permet d\'accéder aux analytics et tableaux de bord',
            'reports.statistics' => 'Permet d\'accéder aux statistiques avancées',
            
            // Gestion API
            'api.access' => 'Permet d\'accéder aux API du système',
            'api.manage' => 'Permet de gérer les clés d\'accès API',
            'api.webhooks' => 'Permet de configurer les webhooks',
            'api.logs' => 'Permet de consulter les logs d\'utilisation API'
        ];
        
        return $descriptions[$name] ?? "Permission pour {$displayName} dans la catégorie {$category}";
    }
    
    /**
     * Valider la cohérence des permissions créées
     */
    private function validatePermissions()
    {
        $this->command->info('🔍 Validation de la cohérence des permissions...');
        
        $errors = [];
        $warnings = [];
        
        // Vérifier que toutes les permissions système sont présentes
        $systemPermissions = collect(Permission::getSystemPermissions())->flatten()->keys();
        $dbPermissions = Permission::pluck('name');
        
        $missing = $systemPermissions->diff($dbPermissions);
        if ($missing->count() > 0) {
            $errors[] = "Permissions manquantes: " . $missing->implode(', ');
        }
        
        // Vérifier les doublons
        $duplicates = Permission::select('name')
                                ->groupBy('name')
                                ->havingRaw('COUNT(*) > 1')
                                ->pluck('name');
        
        if ($duplicates->count() > 0) {
            $errors[] = "Permissions dupliquées: " . $duplicates->implode(', ');
        }
        
        // Vérifier les catégories
        $invalidCategories = Permission::whereNotIn('category', array_keys(Permission::getSystemPermissions()))
                                      ->pluck('name');
        
        if ($invalidCategories->count() > 0) {
            $warnings[] = "Permissions avec catégories non-standard: " . $invalidCategories->implode(', ');
        }
        
        // Vérifier le format des noms
        $invalidNames = Permission::where('name', '!~', '^[a-z]+\\.[a-z_]+$') // « not regexp » MySQL
                                 ->pluck('name');
        
        if ($invalidNames->count() > 0) {
            $warnings[] = "Permissions avec format de nom non-standard: " . $invalidNames->implode(', ');
        }
        
        // Afficher les résultats
        if (count($errors) > 0) {
            $this->command->error('❌ Erreurs détectées:');
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }
        
        if (count($warnings) > 0) {
            $this->command->warn('⚠️  Avertissements:');
            foreach ($warnings as $warning) {
                $this->command->warn("  - {$warning}");
            }
        }
        
        if (count($errors) === 0 && count($warnings) === 0) {
            $this->command->info('✅ Toutes les permissions sont cohérentes !');
        }
        
        // Statistiques par catégorie
        $this->command->info('📊 Répartition par catégorie:');
        $stats = Permission::select('category')
                          ->selectRaw('COUNT(*) as count')
                          ->groupBy('category')
                          ->orderBy('count', 'desc')
                          ->get();
        
        foreach ($stats as $stat) {
            $this->command->info("  - {$stat->category}: {$stat->count} permissions");
        }
    }
}