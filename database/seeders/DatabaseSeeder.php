<?php
/**
 * DATABASE SEEDER PRINCIPAL - PNGDI
 * Orchestration complète de l'initialisation des données système
 * Compatible PHP 7.3.29 - Laravel
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->command->info('');
        $this->command->info('🇬🇦 ===============================================');
        $this->command->info('🇬🇦   INITIALISATION SYSTÈME PNGDI - GABON    🇬🇦');
        $this->command->info('🇬🇦 ===============================================');
        $this->command->info('');
        
        $startTime = microtime(true);
        
        // Ordre important pour les relations entre tables
        $seeders = [
            [
                'class' => PermissionSeeder::class,
                'name' => 'Permissions Système',
                'description' => 'Création des 60+ permissions granulaires PNGDI'
            ],
            [
                'class' => RoleSeeder::class,
                'name' => 'Rôles Système',
                'description' => 'Création des 8 rôles gabonais avec attributions'
            ],
            [
                'class' => SuperAdminSeeder::class,
                'name' => 'Utilisateurs Système',
                'description' => 'Création du Super Admin et comptes de test'
            ],
            [
                'class' => GeolocalisationSeeder::class,
                'name' => 'Géolocalisation Gabon',
                'description' => 'Peuplement des 9 provinces et subdivisions administratives'
            ]
        ];
        
        foreach ($seeders as $index => $seederInfo) {
            $step = $index + 1;
            $total = count($seeders);
            
            $this->command->info("📋 ÉTAPE {$step}/{$total}: {$seederInfo['name']}");
            $this->command->info("ℹ️  {$seederInfo['description']}");
            $this->command->info('');
            
            $stepStart = microtime(true);
            
            try {
                $this->call($seederInfo['class']);
                
                $stepDuration = round((microtime(true) - $stepStart) * 1000, 2);
                $this->command->info("✅ {$seederInfo['name']} terminé en {$stepDuration}ms");
                
            } catch (\Exception $e) {
                $this->command->error("❌ Erreur lors de {$seederInfo['name']}: " . $e->getMessage());
                throw $e;
            }
            
            $this->command->info('');
        }
        
        // Statistiques finales
        $this->displayFinalStats();
        
        $totalDuration = round((microtime(true) - $startTime) * 1000, 2);
        
        $this->command->info('🎉 ===============================================');
        $this->command->info('🎉   INITIALISATION PNGDI TERMINÉE AVEC SUCCÈS  ');
        $this->command->info("🎉   Durée totale: {$totalDuration}ms");
        $this->command->info('🎉 ===============================================');
        $this->command->info('');
        
        // Instructions post-installation
        $this->displayPostInstallInstructions();
    }
    
    /**
     * Afficher les statistiques finales du système
     */
    private function displayFinalStats()
    {
        $this->command->info('📊 STATISTIQUES SYSTÈME FINAL');
        $this->command->info('================================');
        
        // Statistiques des permissions
        $permissionsCount = \App\Models\Permission::count();
        $categoriesCount = \App\Models\Permission::distinct('category')->count();
        
        $this->command->info("🔑 Permissions créées: {$permissionsCount}");
        $this->command->info("📂 Catégories: {$categoriesCount}");
        
        // Statistiques des rôles
        $rolesCount = \App\Models\Role::count();
        $activeRolesCount = \App\Models\Role::where('is_active', true)->count();
        
        $this->command->info("🎭 Rôles créés: {$rolesCount}");
        $this->command->info("✅ Rôles actifs: {$activeRolesCount}");
        
        // Statistiques des utilisateurs
        $usersCount = \App\Models\User::count();
        $activeUsersCount = \App\Models\User::where('is_active', true)->count();
        $verifiedUsersCount = \App\Models\User::where('is_verified', true)->count();
        $newSystemUsersCount = \App\Models\User::whereNotNull('role_id')->count();
        
        $this->command->info("👥 Utilisateurs créés: {$usersCount}");
        $this->command->info("✅ Utilisateurs actifs: {$activeUsersCount}");
        $this->command->info("🔐 Utilisateurs vérifiés: {$verifiedUsersCount}");
        $this->command->info("🆕 Nouveau système: {$newSystemUsersCount}");
        
        // Répartition par rôle avec couleurs
        $this->command->info('');
        $this->command->info('🎨 RÉPARTITION PAR RÔLES (COULEURS GABONAISES):');
        
        $roleStats = \App\Models\User::whereNotNull('role_id')
                                   ->join('roles', 'users.role_id', '=', 'roles.id')
                                   ->selectRaw('roles.display_name, roles.color, roles.level, COUNT(*) as count')
                                   ->groupBy('roles.id', 'roles.display_name', 'roles.color', 'roles.level')
                                   ->orderBy('roles.level', 'desc')
                                   ->get();
        
        foreach ($roleStats as $stat) {
            $colorEmoji = $this->getColorEmoji($stat->color);
            $this->command->info("{$colorEmoji} {$stat->display_name}: {$stat->count} utilisateur(s) (Niveau {$stat->level})");
        }
        
        $this->command->info('');
    }
    
    /**
     * Obtenir l'emoji correspondant à une couleur gabonaise
     */
    private function getColorEmoji($color)
    {
        $emojis = [
            '#009e3f' => '🟢', // Vert gabonais
            '#ffcd00' => '🟡', // Jaune gabonais
            '#003f7f' => '🔵', // Bleu gabonais
            '#8b1538' => '🔴', // Rouge gabonais
            '#17a2b8' => '🔷', // Cyan
            '#28a745' => '💚', // Vert
            '#6c757d' => '⚫'  // Gris
        ];
        
        return $emojis[$color] ?? '🎯';
    }
    
    /**
     * Afficher les instructions post-installation
     */
    private function displayPostInstallInstructions()
    {
        $this->command->info('📋 INSTRUCTIONS POST-INSTALLATION');
        $this->command->info('==================================');
        $this->command->info('');
        
        $this->command->info('🌐 1. ACCÈS AU SYSTÈME:');
        $this->command->info('   URL: http://localhost:8000/admin');
        $this->command->info('   Ou: http://127.0.0.1:8000/admin');
        $this->command->info('');
        
        $this->command->info('👤 2. COMPTE SUPER ADMINISTRATEUR:');
        $this->command->info('   📧 Email: admin@pngdi.ga');
        $this->command->info('   🔑 Mot de passe: Admin@PNGDI2025!');
        $this->command->info('   🎭 Rôle: Super Administrateur (Toutes permissions)');
        $this->command->info('');
        
        $this->command->info('🎯 3. FONCTIONNALITÉS DISPONIBLES:');
        $this->command->info('   ✅ Système de rôles et permissions granulaires');
        $this->command->info('   ✅ Interface avec couleurs gabonaises officielles');
        $this->command->info('   ✅ Gestion workflow des organisations');
        $this->command->info('   ✅ Audit trail des sessions utilisateurs');
        $this->command->info('   ✅ Double système de rôles (compatibilité)');
        $this->command->info('');
        
        $this->command->info('🔧 4. PROCHAINES ÉTAPES RECOMMANDÉES:');
        $this->command->info('   1. Tester la connexion avec le compte Super Admin');
        $this->command->info('   2. Vérifier les pages workflow admin (/admin/workflow/*)');
        $this->command->info('   3. Configurer les paramètres spécifiques à votre environnement');
        $this->command->info('   4. Changer les mots de passe par défaut en production');
        $this->command->info('   5. Configurer les notifications email');
        $this->command->info('');
        
        $this->command->info('⚠️  5. SÉCURITÉ IMPORTANTE:');
        $this->command->warn('   🔐 Changez IMMÉDIATEMENT les mots de passe en production');
        $this->command->warn('   🔒 Activez la 2FA pour les comptes administrateurs');
        $this->command->warn('   🛡️  Configurez les restrictions IP si nécessaire');
        $this->command->warn('   📱 Surveillez les sessions suspectes dans l\'audit trail');
        $this->command->info('');
        
        $this->command->info('🎨 6. PERSONNALISATION GABONAISE:');
        $this->command->info('   🇬🇦 Couleurs du drapeau gabonais intégrées');
        $this->command->info('   🏢 Rôles adaptés aux structures gabonaises');
        $this->command->info('   📍 Détection des IP gabonaises configurée');
        $this->command->info('   🌍 Géolocalisation Libreville par défaut');
        $this->command->info('');
        
        $this->command->info('💡 7. SUPPORT ET DOCUMENTATION:');
        $this->command->info('   📚 Documentation complète dans les commentaires du code');
        $this->command->info('   🔍 Utilisez les scopes et méthodes métier intégrées');
        $this->command->info('   📊 Exploitez les statistiques et rapports intégrés');
        $this->command->info('   🛠️  Étendez le système selon vos besoins spécifiques');
        $this->command->info('');
        
        $this->command->info('🚀 Le système PNGDI est maintenant prêt à l\'utilisation !');
        $this->command->info('🇬🇦 Bonne utilisation du Portail National Gabonais ! 🇬🇦');
    }
}