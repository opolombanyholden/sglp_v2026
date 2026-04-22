<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DocumentTemplateController;
use App\Http\Controllers\Admin\GeneratedDocumentController;
use App\Http\Controllers\Admin\DocumentCustomizationController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController as PublicDocVerificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DossierController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\DepartementController;
use App\Http\Controllers\Admin\CommuneVilleController;
use App\Http\Controllers\Admin\ArrondissementController;
use App\Http\Controllers\Admin\CantonController;
use App\Http\Controllers\Admin\RegroupementController;
use App\Http\Controllers\Admin\LocaliteController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReferentielController;
use App\Http\Controllers\Admin\OrganisationTypeController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\FonctionController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\NipDatabaseController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Http\Controllers\Admin\ValidationEntityController;
use App\Http\Controllers\Admin\GeographyController;
use App\Http\Controllers\Admin\WorkflowStepController;
use App\Http\Controllers\Admin\DomaineActiviteController;
use App\Http\Controllers\Admin\OperationController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Routes de connexion Admin (portail séparé)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| Routes Administration - SGLP/PNGDI - VERSION CORRIGÉE v2.3
|--------------------------------------------------------------------------
| Routes pour l'interface d'administration complète
| Middleware : auth, verified, admin
| ✅ Version corrigée sans doublons de noms de routes
| ✅ Compatible PHP 8.3 et Laravel 9
| ✅ MODULE TYPES D'ORGANISATIONS AJOUTÉ
| ✅ MODULE DOCUMENTS - ROUTES COMPLÈTES (21/01/2025)
| ✅ MODULE ROLES - ROUTES COMPLÈTES CORRIGÉES (08/11/2025)
| ✅ MODULE PERMISSIONS - ROUTES COMPLÈTES AJOUTÉES (11/11/2025)
| ✅ MODULE NIP DATABASE - ROUTES CLEANUP ET VERIFY AJOUTÉES (21/11/2025)
| ✅ MODULE FONCTIONS MEMBRES - ROUTES COMPLÈTES AJOUTÉES (24/11/2025)
| ❌ ROUTES PUBLIQUES ET API SUPPRIMÉES (maintenant dans web.php et api.php)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 🏠 DASHBOARD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    /*
    |--------------------------------------------------------------------------
    | 📊 ANALYTICS ET RAPPORTS - SECTION COMPLÈTE
    |--------------------------------------------------------------------------
    */
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports.index');
    Route::get('/exports', [AnalyticsController::class, 'exports'])->name('exports.index');
    Route::get('/activity-logs', [AnalyticsController::class, 'activityLogs'])->name('activity-logs.index');

    // 📤 EXPORTS - Routes complètes
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'exports'])->name('index');
        Route::get('/global', [AnalyticsController::class, 'exportGlobal'])->name('global');
        Route::get('/dossiers', [AnalyticsController::class, 'exportDossiers'])->name('dossiers');
        Route::get('/users', [AnalyticsController::class, 'exportUsers'])->name('users');
        Route::get('/organisations', [AnalyticsController::class, 'exportOrganisations'])->name('organisations');

        // Exports spécialisés
        Route::post('/dossiers-en-attente', [AnalyticsController::class, 'dossiersEnAttente'])->name('dossiers-en-attente');
        Route::post('/dossiers-agent/{agentId}', [AnalyticsController::class, 'dossiersAgent'])->name('dossiers-agent');
        Route::post('/organisations-par-type', [AnalyticsController::class, 'organisationsParType'])->name('organisations-par-type');
        Route::post('/rapport-activite', [AnalyticsController::class, 'rapportActivite'])->name('rapport-activite');
        Route::post('/rapport-performance', [AnalyticsController::class, 'rapportPerformance'])->name('rapport-performance');
        Route::post('/statistiques', [AnalyticsController::class, 'statistiques'])->name('statistiques');
        Route::get('/format/{type}/{format}', [AnalyticsController::class, 'downloadFormat'])
            ->name('format')
            ->where('format', 'excel|pdf|csv|json');
    });

    // 📊 REPORTS - Routes complètes  
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
        Route::get('/monthly', [AnalyticsController::class, 'monthlyReport'])->name('monthly');
        Route::get('/annual', [AnalyticsController::class, 'annualReport'])->name('annual');
        Route::get('/custom', [AnalyticsController::class, 'customReport'])->name('custom');
    });

    // 📈 ACTIVITY LOGS - Routes complètes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'activityLogs'])->name('index');
        Route::get('/search', [AnalyticsController::class, 'searchLogs'])->name('search');
        Route::delete('/clean', [AnalyticsController::class, 'cleanLogs'])->name('clean');
        Route::get('/export', [AnalyticsController::class, 'exportLogs'])->name('export');
    });


    /*
    |--------------------------------------------------------------------------
    | 📄 WORKFLOW DES DOSSIERS - ROUTES CORRIGÉES
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/en-attente', [WorkflowController::class, 'enAttente'])->name('en-attente');
        Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
        Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
        Route::get('/rejetes', [WorkflowController::class, 'rejetes'])->name('rejetes');
        Route::get('/archives', [WorkflowController::class, 'archives'])->name('archives');

        // Actions workflow
        Route::post('/{dossier}/assign', [WorkflowController::class, 'assign'])->name('assign');
        Route::post('/{dossier}/validate', [DossierController::class, 'validateDossier'])->name('validate');
        Route::post('/{dossier}/reject', [WorkflowController::class, 'reject'])->name('reject');
        Route::post('/step/{stepId}/complete', [WorkflowController::class, 'completeStep'])->name('step.complete');
        Route::post('/step/{stepId}/skip', [WorkflowController::class, 'skipStep'])->name('step.skip');
        Route::post('/reset/{dossierId}', [WorkflowController::class, 'resetWorkflow'])->name('reset');

        // Configuration workflow
        Route::get('/templates', [WorkflowController::class, 'templates'])->name('templates');
        Route::post('/templates', [WorkflowController::class, 'saveTemplate'])->name('templates.save');
    });

    /*
    |--------------------------------------------------------------------------
    | 🏢 GESTION DES ORGANISATIONS - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('organisations')->name('organisations.')->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create', [DossierController::class, 'createOrganisation'])->name('create');
        Route::post('/', [DossierController::class, 'storeOrganisation'])->name('store');
        Route::get('/{organisation}', [DossierController::class, 'showOrganisation'])->name('show');
        Route::get('/{organisation}/edit', [DossierController::class, 'editOrganisation'])->name('edit');
        Route::put('/{organisation}', [DossierController::class, 'updateOrganisation'])->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | 📋 OPÉRATIONS SUR ORGANISATIONS - ROUTES COMPLÈTES (8 routes)
    |--------------------------------------------------------------------------
    | Gestion des opérations : modification, cessation, ajout/retrait adhérent,
    | déclaration d'activité, changement statutaire
    | ✅ Ajouté le : 28/12/2025
    |--------------------------------------------------------------------------
    */
    Route::prefix('operations')->name('operations.')->group(function () {
        // Sélection d'organisation
        Route::get('/select-organisation', [OperationController::class, 'selectOrganisation'])->name('select-organisation');

        // Sélection de l'opération pour une organisation
        Route::get('/{organisation}/select-operation', [OperationController::class, 'selectOperation'])->name('select-operation');

        // Sélection des champs à modifier (étape préalable pour modifications)
        Route::get('/{organisation}/modification/fields', [OperationController::class, 'selectModificationFields'])->name('modification.fields');

        // Formulaires de création par type d'opération
        Route::get('/{organisation}/{operationType}/create', [OperationController::class, 'create'])->name('create');

        // Enregistrement de l'opération
        Route::post('/{organisation}/{operationType}/store', [OperationController::class, 'store'])->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | CORRECTIONS ADMINISTRATIVES
    |--------------------------------------------------------------------------
    */
    Route::prefix('corrections')->name('corrections.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CorrectionController::class, 'index'])->name('index');
        Route::get('/select-organisation', [\App\Http\Controllers\Admin\CorrectionController::class, 'selectOrganisation'])->name('select-organisation');
        Route::get('/{organisation}/create', [\App\Http\Controllers\Admin\CorrectionController::class, 'create'])->name('create');
        Route::post('/{organisation}/store', [\App\Http\Controllers\Admin\CorrectionController::class, 'store'])->name('store');
        Route::get('/{dossier}/review', [\App\Http\Controllers\Admin\CorrectionController::class, 'review'])->name('review');
        Route::post('/{dossier}/approve', [\App\Http\Controllers\Admin\CorrectionController::class, 'approve'])->name('approve');
        Route::post('/{dossier}/reject', [\App\Http\Controllers\Admin\CorrectionController::class, 'reject'])->name('reject');
    });

    /*
    |--------------------------------------------------------------------------
    | 🗺️ API GÉOLOCALISATION - ROUTES AJAX (pour formulaires dynamiques)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/geolocation')->name('api.geolocation.')->group(function () {
        Route::get('/provinces', [DossierController::class, 'getProvinces'])->name('provinces');
        Route::get('/departements/{province_id}', [DossierController::class, 'getDepartements'])->name('departements');
        Route::get('/communes/{departement_id}', [DossierController::class, 'getCommunes'])->name('communes');
        Route::get('/arrondissements/{commune_id}', [DossierController::class, 'getArrondissements'])->name('arrondissements');
        Route::get('/cantons/{arrondissement_id}', [DossierController::class, 'getCantons'])->name('cantons');
        Route::get('/regroupements/{canton_id}', [DossierController::class, 'getRegroupements'])->name('regroupements');
        Route::get('/localites/{regroupement_id}', [DossierController::class, 'getLocalites'])->name('localites');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔔 NOTIFICATIONS - ROUTES CORRIGÉES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
    });

    /*
    |--------------------------------------------------------------------------
    | 📁 DOSSIERS - ROUTES COMPLÈTES (23 routes)
    |--------------------------------------------------------------------------
    */
    // Suggestions (Fonctions et Domaines proposés par les usagers)
    Route::prefix('suggestions')->name('suggestions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SuggestionController::class, 'adminIndex'])->name('index');
        Route::post('/{type}/{id}/approve', [\App\Http\Controllers\SuggestionController::class, 'adminApprove'])->name('approve');
        Route::post('/{type}/{id}/reject', [\App\Http\Controllers\SuggestionController::class, 'adminReject'])->name('reject');
    });

    Route::prefix('dossiers')->name('dossiers.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');

        // Sauvegarde progressive par étape (AJAX) - brouillon admin
        Route::post('/save-draft-step', [DossierController::class, 'saveDraftStep'])->name('save-draft-step');

        // API Configuration type d'organisation (pour formulaire création)
        Route::get('/type-config/{id}', [DossierController::class, 'getTypeConfig'])->name('type-config');

        // Statuts
        Route::get('/en-attente', [DossierController::class, 'enAttente'])->name('en-attente');
        Route::get('/en-cours', [DossierController::class, 'enCours'])->name('en-cours');
        Route::get('/valides', [DossierController::class, 'valides'])->name('valides');
        Route::get('/rejetes', [DossierController::class, 'rejetes'])->name('rejetes');
        Route::get('/brouillons', [DossierController::class, 'brouillons'])->name('brouillons');
        Route::get('/annules', [DossierController::class, 'annules'])->name('annules');
        Route::get('/supprimes', [DossierController::class, 'supprimes'])->name('supprimes'); // Super admin only

        // Actions spécifiques
        Route::post('/assign-batch', [DossierController::class, 'assignBatch'])->name('assign-batch');
        Route::post('/export', [DossierController::class, 'export'])->name('export');
        Route::get('/stats', [DossierController::class, 'stats'])->name('stats');
        Route::get('/search', [DossierController::class, 'search'])->name('search');
        // Queue FIFO et calcul de position (pour modal assignation)
        Route::get('/queue-preview', [DossierController::class, 'queuePreview'])->name('queue-preview');
        Route::post('/calculate-position', [DossierController::class, 'calculatePosition'])->name('calculate-position');
        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::delete('/{dossier}', [DossierController::class, 'destroy'])->name('destroy');

        // Actions sur dossiers
        Route::post('/{dossier}/assign', [DossierController::class, 'assign'])->name('assign');
        Route::post('/{dossier}/validate', [DossierController::class, 'validateDossier'])->name('validate');
        Route::post('/{dossier}/reject', [DossierController::class, 'reject'])->name('reject');
        Route::post('/{dossier}/archive', [DossierController::class, 'archive'])->name('archive');
        Route::post('/{dossier}/restore', [DossierController::class, 'restore'])->name('restore');
        Route::post('/{dossier}/cancel', [DossierController::class, 'cancel'])->name('cancel');
        Route::delete('/{dossier}/delete-permanently', [DossierController::class, 'deletePermanently'])->name('delete-permanently');
        Route::get('/{dossier}/history', [DossierController::class, 'history'])->name('history');
        Route::get('/{dossier}/documents', [DossierController::class, 'documents'])->name('documents');
        Route::post('/{dossier}/generate-document', [DossierController::class, 'generateDocument'])->name('generate-document');

        // Demande de modifications et gestion brouillon
        Route::post('/{dossier}/request-modification', [DossierController::class, 'requestModification'])->name('request-modification');
        Route::post('/{dossier}/set-brouillon', [DossierController::class, 'setBrouillon'])->name('set-brouillon');
        Route::post('/{dossier}/comment', [DossierController::class, 'addComment'])->name('comment');

        // Téléchargements PDF
        Route::get('/{dossier}/accuse-reception', [DossierController::class, 'downloadAccuseReception'])->name('accuse-reception');
        Route::get('/{dossier}/recepisse-provisoire', [DossierController::class, 'downloadRecepisseProvisoire'])->name('recepisse-provisoire');
        Route::get('/{dossier}/recepisse-definitif', [DossierController::class, 'downloadRecepisseDefinitif'])->name('recepisse-definitif');
        Route::post('/{dossier}/request-supplement', [DossierController::class, 'requestSupplement'])->name('request-supplement');

        // Consultation et rapport des anomalies adhérents - Admin
        Route::get('/{dossier}/consulter-anomalies', [DossierController::class, 'consulterAnomalies'])->name('consulter-anomalies');
        Route::get('/{dossier}/rapport-anomalies', [DossierController::class, 'rapportAnomalies'])->name('rapport-anomalies');
    });

    /*
    |--------------------------------------------------------------------------
    | ADHERENTS - Consultation et validation par organisation (Admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('organisations/{organisation}/adherents')->name('adherents.')->group(function () {
        Route::get('/', [DossierController::class, 'organisationAdherents'])->name('index');
        Route::get('/pending', [DossierController::class, 'adminPendingRegistrations'])->name('pending');
        Route::get('/{adherent}', [DossierController::class, 'showAdherent'])->name('show');
        Route::post('/{adherent}/validate', [DossierController::class, 'adminValidateRegistration'])->name('validate');
        Route::post('/{adherent}/reject', [DossierController::class, 'adminRejectRegistration'])->name('reject');
    });

    /*
    |--------------------------------------------------------------------------
    | 📝 PERSONNALISATION DE DOCUMENTS - ROUTES (3 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        // Édition des en-têtes et signatures avant génération
        Route::get('/dossiers/{dossier}/templates/{template}/customize', [DocumentCustomizationController::class, 'edit'])
            ->name('customize');

        // Sauvegarde et génération
        Route::post('/dossiers/{dossier}/save-customization', [DocumentCustomizationController::class, 'store'])
            ->name('save-customization');

        // API pour récupérer les personnalisations
        Route::get('/dossiers/{dossier}/templates/{template}/customization', [DocumentCustomizationController::class, 'getCustomization'])
            ->name('get-customization');
    });

    /*
    |--------------------------------------------------------------------------
    | 👥 GESTION DES UTILISATEURS - ROUTES COMPLÈTES (24 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        // Liste des utilisateurs par type
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/operators', [UserManagementController::class, 'operators'])->name('operators');
        Route::get('/agents', [UserManagementController::class, 'agents'])->name('agents');

        // Création
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');

        // Actions spécifiques
        Route::get('/search', [UserManagementController::class, 'search'])->name('search');
        Route::post('/bulk-operations', [UserManagementController::class, 'bulkOperations'])->name('bulk-operations');
        Route::post('/export', [UserManagementController::class, 'export'])->name('export');
        Route::get('/statistics', [UserManagementController::class, 'statistics'])->name('statistics');
        Route::get('/export/excel', [UserManagementController::class, 'exportExcel'])->name('export.excel');
        Route::get('/import-template', [UserManagementController::class, 'downloadImportTemplate'])->name('import-template');
        Route::post('/import', [UserManagementController::class, 'import'])->name('import');

        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{id}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('destroy');

        // Actions sur utilisateur
        Route::get('/{id}/check-constraints', [UserManagementController::class, 'checkConstraints'])->name('check-constraints');
        Route::post('/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{id}/force-verify-email', [UserManagementController::class, 'forceVerifyEmail'])->name('force-verify-email');
        Route::post('/{id}/send-credentials', [UserManagementController::class, 'sendCredentials'])->name('send-credentials');
        Route::get('/{id}/activity', [UserManagementController::class, 'activity'])->name('activity');
        Route::get('/{id}/dossiers', [UserManagementController::class, 'dossiers'])->name('dossiers');
        Route::post('/{id}/assign-role', [UserManagementController::class, 'assignRole'])->name('assign-role');
        Route::post('/{id}/remove-role', [UserManagementController::class, 'removeRole'])->name('remove-role');
        Route::post('/{id}/sync-permissions', [UserManagementController::class, 'syncPermissions'])->name('sync-permissions');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔐 GESTION DES RÔLES - ROUTES COMPLÈTES (20 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        Route::get('/', [RolesController::class, 'index'])->name('index');
        Route::get('/create', [RolesController::class, 'create'])->name('create');
        Route::post('/', [RolesController::class, 'store'])->name('store');

        // Actions spécifiques avant {role}
        Route::get('/search', [RolesController::class, 'search'])->name('search');
        Route::post('/bulk-operations', [RolesController::class, 'bulkOperations'])->name('bulk-operations');
        Route::post('/init-system', [RolesController::class, 'initSystemRoles'])->name('init-system');
        Route::get('/export', [RolesController::class, 'export'])->name('export');
        Route::post('/import', [RolesController::class, 'import'])->name('import');
        Route::get('/statistics', [RolesController::class, 'statistics'])->name('statistics');

        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{role}', [RolesController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RolesController::class, 'update'])->name('update');
        Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');

        // Actions sur rôle
        Route::post('/{role}/duplicate', [RolesController::class, 'duplicate'])->name('duplicate');
        Route::post('/{role}/toggle-status', [RolesController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{role}/sync-permissions', [RolesController::class, 'syncPermissions'])->name('sync-permissions');
        Route::get('/{role}/users', [RolesController::class, 'users'])->name('users');
        Route::get('/{role}/permissions', [RolesController::class, 'permissions'])->name('permissions');
        Route::post('/{role}/assign-users', [RolesController::class, 'assignUsers'])->name('assign-users');
        Route::post('/{role}/remove-users', [RolesController::class, 'removeUsers'])->name('remove-users');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔑 GESTION DES PERMISSIONS - ROUTES COMPLÈTES (11 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::get('/create', [PermissionsController::class, 'create'])->name('create');
        Route::post('/', [PermissionsController::class, 'store'])->name('store');
        Route::get('/search', [PermissionsController::class, 'search'])->name('search');
        Route::post('/bulk-operations', [PermissionsController::class, 'bulkOperations'])->name('bulk-operations');
        Route::delete('/bulk-delete', [PermissionsController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/init-system-permissions', [PermissionsController::class, 'initSystemPermissions'])->name('init-system-permissions');
        Route::get('/export', [PermissionsController::class, 'export'])->name('export');

        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{permission}', [PermissionsController::class, 'show'])->name('show');
        Route::get('/{permission}/edit', [PermissionsController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionsController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionsController::class, 'destroy'])->name('destroy');
        Route::get('/{permission}/roles', [PermissionsController::class, 'roles'])->name('roles');
    });

    /*
    |--------------------------------------------------------------------------
    | 🎯 MATRICE DES PERMISSIONS - ROUTES (4 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('permission-matrix')->name('permission-matrix.')->group(function () {
        Route::get('/', [PermissionMatrixController::class, 'index'])->name('index');
        Route::post('/update', [PermissionMatrixController::class, 'update'])->name('update');
        Route::get('/export', [PermissionMatrixController::class, 'export'])->name('export');
        Route::post('/reset', [PermissionMatrixController::class, 'reset'])->name('reset');
    });

    /*
    |--------------------------------------------------------------------------
    | 🗺️ MODULE GÉOGRAPHIE - GESTION COMPLÈTE (70 routes)
    |--------------------------------------------------------------------------
    */

    // 🌍 PROVINCES (10 routes)
    Route::prefix('provinces')->name('provinces.')->group(function () {
        Route::get('/', [ProvinceController::class, 'index'])->name('index');
        Route::get('/create', [ProvinceController::class, 'create'])->name('create');
        Route::post('/', [ProvinceController::class, 'store'])->name('store');
        Route::get('/export', [ProvinceController::class, 'export'])->name('export');
        Route::post('/import', [ProvinceController::class, 'import'])->name('import');
        Route::get('/{province}', [ProvinceController::class, 'show'])->name('show');
        Route::get('/{province}/edit', [ProvinceController::class, 'edit'])->name('edit');
        Route::put('/{province}', [ProvinceController::class, 'update'])->name('update');
        Route::delete('/{province}', [ProvinceController::class, 'destroy'])->name('destroy');
        Route::post('/{province}/toggle-status', [ProvinceController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 🏛️ DÉPARTEMENTS (10 routes)
    Route::prefix('departements')->name('departements.')->group(function () {
        Route::get('/', [DepartementController::class, 'index'])->name('index');
        Route::get('/create', [DepartementController::class, 'create'])->name('create');
        Route::post('/', [DepartementController::class, 'store'])->name('store');
        Route::get('/export', [DepartementController::class, 'export'])->name('export');
        Route::post('/import', [DepartementController::class, 'import'])->name('import');
        Route::get('/{departement}', [DepartementController::class, 'show'])->name('show');
        Route::get('/{departement}/edit', [DepartementController::class, 'edit'])->name('edit');
        Route::put('/{departement}', [DepartementController::class, 'update'])->name('update');
        Route::delete('/{departement}', [DepartementController::class, 'destroy'])->name('destroy');
        Route::post('/{departement}/toggle-status', [DepartementController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 🏘️ COMMUNES / VILLES (10 routes)
    Route::prefix('communes')->name('communes.')->group(function () {
        Route::get('/', [CommuneVilleController::class, 'index'])->name('index');
        Route::get('/create', [CommuneVilleController::class, 'create'])->name('create');
        Route::post('/', [CommuneVilleController::class, 'store'])->name('store');
        Route::get('/export', [CommuneVilleController::class, 'export'])->name('export');
        Route::post('/import', [CommuneVilleController::class, 'import'])->name('import');
        Route::get('/{commune}', [CommuneVilleController::class, 'show'])->name('show');
        Route::get('/{commune}/edit', [CommuneVilleController::class, 'edit'])->name('edit');
        Route::put('/{commune}', [CommuneVilleController::class, 'update'])->name('update');
        Route::delete('/{commune}', [CommuneVilleController::class, 'destroy'])->name('destroy');
        Route::post('/{commune}/toggle-status', [CommuneVilleController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 📍 ARRONDISSEMENTS (10 routes)
    Route::prefix('arrondissements')->name('arrondissements.')->group(function () {
        Route::get('/', [ArrondissementController::class, 'index'])->name('index');
        Route::get('/create', [ArrondissementController::class, 'create'])->name('create');
        Route::post('/', [ArrondissementController::class, 'store'])->name('store');
        Route::get('/export', [ArrondissementController::class, 'export'])->name('export');
        Route::post('/import', [ArrondissementController::class, 'import'])->name('import');
        Route::get('/{arrondissement}', [ArrondissementController::class, 'show'])->name('show');
        Route::get('/{arrondissement}/edit', [ArrondissementController::class, 'edit'])->name('edit');
        Route::put('/{arrondissement}', [ArrondissementController::class, 'update'])->name('update');
        Route::delete('/{arrondissement}', [ArrondissementController::class, 'destroy'])->name('destroy');
        Route::post('/{arrondissement}/toggle-status', [ArrondissementController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 🎋 CANTONS (10 routes)
    Route::prefix('cantons')->name('cantons.')->group(function () {
        Route::get('/', [CantonController::class, 'index'])->name('index');
        Route::get('/create', [CantonController::class, 'create'])->name('create');
        Route::post('/', [CantonController::class, 'store'])->name('store');
        Route::get('/export', [CantonController::class, 'export'])->name('export');
        Route::post('/import', [CantonController::class, 'import'])->name('import');
        Route::get('/{canton}', [CantonController::class, 'show'])->name('show');
        Route::get('/{canton}/edit', [CantonController::class, 'edit'])->name('edit');
        Route::put('/{canton}', [CantonController::class, 'update'])->name('update');
        Route::delete('/{canton}', [CantonController::class, 'destroy'])->name('destroy');
        Route::post('/{canton}/toggle-status', [CantonController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 🏘️ REGROUPEMENTS (10 routes)
    Route::prefix('regroupements')->name('regroupements.')->group(function () {
        Route::get('/', [RegroupementController::class, 'index'])->name('index');
        Route::get('/create', [RegroupementController::class, 'create'])->name('create');
        Route::post('/', [RegroupementController::class, 'store'])->name('store');
        Route::get('/export', [RegroupementController::class, 'export'])->name('export');
        Route::post('/import', [RegroupementController::class, 'import'])->name('import');
        Route::get('/{regroupement}', [RegroupementController::class, 'show'])->name('show');
        Route::get('/{regroupement}/edit', [RegroupementController::class, 'edit'])->name('edit');
        Route::put('/{regroupement}', [RegroupementController::class, 'update'])->name('update');
        Route::delete('/{regroupement}', [RegroupementController::class, 'destroy'])->name('destroy');
        Route::post('/{regroupement}/toggle-status', [RegroupementController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 📌 LOCALITÉS (10 routes)
    Route::prefix('localites')->name('localites.')->group(function () {
        Route::get('/', [LocaliteController::class, 'index'])->name('index');
        Route::get('/create', [LocaliteController::class, 'create'])->name('create');
        Route::post('/', [LocaliteController::class, 'store'])->name('store');
        Route::get('/export', [LocaliteController::class, 'export'])->name('export');
        Route::post('/import', [LocaliteController::class, 'import'])->name('import');
        Route::get('/{localite}', [LocaliteController::class, 'show'])->name('show');
        Route::get('/{localite}/edit', [LocaliteController::class, 'edit'])->name('edit');
        Route::put('/{localite}', [LocaliteController::class, 'update'])->name('update');
        Route::delete('/{localite}', [LocaliteController::class, 'destroy'])->name('destroy');
        Route::post('/{localite}/toggle-status', [LocaliteController::class, 'toggleStatus'])->name('toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | 📚 RÉFÉRENTIELS - TYPES D'ORGANISATIONS, DOCUMENTS ET FONCTIONS (27 routes)
    |--------------------------------------------------------------------------
    | ✅ MODULE FONCTIONS MEMBRES AJOUTÉ (24/11/2025) - 10 routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        Route::get('/types-organisations', [ReferentielController::class, 'typesOrganisations'])->name('types-organisations');
        Route::get('/types-documents', [ReferentielController::class, 'typesDocuments'])->name('types-documents');

        // 🏢 TYPES D'ORGANISATIONS (7 routes) - DANS LE GROUPE REFERENTIELS
        Route::prefix('organisation-types')->name('organisation-types.')->group(function () {
            Route::get('/', [OrganisationTypeController::class, 'index'])->name('index');
            Route::get('/create', [OrganisationTypeController::class, 'create'])->name('create');
            Route::post('/', [OrganisationTypeController::class, 'store'])->name('store');
            Route::get('/{organisationType}', [OrganisationTypeController::class, 'show'])->name('show');
            Route::get('/{organisationType}/edit', [OrganisationTypeController::class, 'edit'])->name('edit');
            Route::put('/{organisationType}', [OrganisationTypeController::class, 'update'])->name('update');
            Route::delete('/{organisationType}', [OrganisationTypeController::class, 'destroy'])->name('destroy');
        });

        // 📄 TYPES DE DOCUMENTS (7 routes) - DANS LE GROUPE REFERENTIELS
        Route::prefix('document-types')->name('document-types.')->group(function () {
            Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
            Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
            Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
            Route::get('/{documentType}', [DocumentTypeController::class, 'show'])->name('show');
            Route::get('/{documentType}/edit', [DocumentTypeController::class, 'edit'])->name('edit');
            Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
            Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
        });

        // 🏭 DOMAINES D'ACTIVITÉ
        Route::prefix('domaines-activite')->name('domaines-activite.')->group(function () {
            Route::get('/', [DomaineActiviteController::class, 'index'])->name('index');
            Route::get('/create', [DomaineActiviteController::class, 'create'])->name('create');
            Route::post('/', [DomaineActiviteController::class, 'store'])->name('store');
            Route::get('/{domaineActivite}/edit', [DomaineActiviteController::class, 'edit'])->name('edit');
            Route::put('/{domaineActivite}', [DomaineActiviteController::class, 'update'])->name('update');
            Route::delete('/{domaineActivite}', [DomaineActiviteController::class, 'destroy'])->name('destroy');
            Route::patch('/{domaineActivite}/toggle-status', [DomaineActiviteController::class, 'toggleStatus'])->name('toggle-status');
        });

        // 👤 FONCTIONS DES MEMBRES (10 routes) - ✅ NOUVEAU MODULE 24/11/2025
        Route::prefix('fonctions')->name('fonctions.')->group(function () {
            Route::get('/', [FonctionController::class, 'index'])->name('index');
            Route::get('/create', [FonctionController::class, 'create'])->name('create');
            Route::post('/', [FonctionController::class, 'store'])->name('store');
            Route::post('/reorder', [FonctionController::class, 'reorder'])->name('reorder');
            Route::get('/{fonction}', [FonctionController::class, 'show'])->name('show');
            Route::get('/{fonction}/edit', [FonctionController::class, 'edit'])->name('edit');
            Route::put('/{fonction}', [FonctionController::class, 'update'])->name('update');
            Route::delete('/{fonction}', [FonctionController::class, 'destroy'])->name('destroy');
            Route::patch('/{fonction}/toggle-status', [FonctionController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 🎨 GESTION DE CONTENU - CMS SGLP (12 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('index');
        Route::get('/create', [ContentController::class, 'create'])->name('create');
        Route::post('/', [ContentController::class, 'store'])->name('store');
        Route::get('/{id}', [ContentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ContentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ContentController::class, 'update'])->name('update');
        Route::delete('/{id}', [ContentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [ContentController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ContentController::class, 'unpublish'])->name('unpublish');
        Route::post('/bulk-operations', [ContentController::class, 'bulkOperations'])->name('bulk-operations');
        Route::get('/preview/{id}', [ContentController::class, 'preview'])->name('preview');
        Route::post('/upload-media', [ContentController::class, 'uploadMedia'])->name('upload-media');
    });

    /*
    |--------------------------------------------------------------------------
    | ⚙️ PARAMÈTRES SYSTÈME - CONFIGURATIONS GLOBALES (21 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update-system', [SettingsController::class, 'updateSystemSettings'])->name('update-system');
        Route::post('/update-preferences', [SettingsController::class, 'updateUserPreferences'])->name('update-preferences');

        // ➕ Routes de sécurité
        Route::post('/update-security', [SettingsController::class, 'updateSecuritySettings'])->name('update-security');
        Route::post('/clear-caches', [SettingsController::class, 'clearCaches'])->name('clear-caches');
        Route::post('/clear-logs', [SettingsController::class, 'clearOldLogs'])->name('clear-logs');
        Route::post('/force-2fa', [SettingsController::class, 'force2FAForAdmins'])->name('force-2fa');
        Route::post('/reset-sessions', [SettingsController::class, 'resetAllSessions'])->name('reset-sessions');
        Route::post('/toggle-maintenance', [SettingsController::class, 'toggleMaintenanceMode'])->name('toggle-maintenance');

        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::post('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::post('/security', [SettingsController::class, 'updateSecurity'])->name('security.update');
        Route::get('/email', [SettingsController::class, 'email'])->name('email');
        Route::post('/email', [SettingsController::class, 'updateEmail'])->name('email.update');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/maintenance', [SettingsController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance/enable', [SettingsController::class, 'enableMaintenance'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [SettingsController::class, 'disableMaintenance'])->name('maintenance.disable');
        Route::get('/logs', [SettingsController::class, 'logs'])->name('logs');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('backup.create');
    });

    // 👤 PROFIL UTILISATEUR
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔢 BASE DE DONNÉES NIP - GESTION COMPLÈTE (15 routes)
    |--------------------------------------------------------------------------
    | ✅ CORRECTION v2.2 (21/11/2025) :
    | - Route 'cleanup' ajoutée (POST)
    | - Route 'verify' ajoutée (POST)
    | - Route 'download-template' renommée en 'template' pour cohérence
    |--------------------------------------------------------------------------
    */
    Route::prefix('nip-database')->name('nip-database.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        // Liste et statistiques
        Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
        Route::get('/statistics', [NipDatabaseController::class, 'statistics'])->name('statistics');

        // Recherche et vérification
        Route::get('/search', [NipDatabaseController::class, 'search'])->name('search');
        Route::post('/verify', [NipDatabaseController::class, 'verify'])->name('verify'); // ✅ AJOUTÉE

        // Import/Export
        Route::get('/import', [NipDatabaseController::class, 'import'])->name('import');
        Route::post('/import', [NipDatabaseController::class, 'processImport'])->name('process-import');
        Route::get('/export', [NipDatabaseController::class, 'export'])->name('export');
        Route::get('/template', [NipDatabaseController::class, 'downloadTemplate'])->name('template'); // ✅ NOM COHÉRENT

        // Maintenance
        Route::post('/cleanup', [NipDatabaseController::class, 'cleanup'])->name('cleanup'); // ✅ AJOUTÉE

        // CRUD
        Route::get('/create', [NipDatabaseController::class, 'create'])->name('create');
        Route::post('/', [NipDatabaseController::class, 'store'])->name('store');

        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{id}', [NipDatabaseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [NipDatabaseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NipDatabaseController::class, 'update'])->name('update');
        Route::delete('/{id}', [NipDatabaseController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | 📝 GESTION DES TEMPLATES DE DOCUMENTS (20 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-templates')->name('document-templates.')->group(function () {

        // Liste et création
        Route::get('/', [DocumentTemplateController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTemplateController::class, 'create'])->name('create');
        Route::post('/', [DocumentTemplateController::class, 'store'])->name('store');

        // Actions spécifiques avant {id}
        Route::get('/search', [DocumentTemplateController::class, 'search'])->name('search');
        Route::post('/bulk-operations', [DocumentTemplateController::class, 'bulkOperations'])->name('bulk-operations');
        Route::get('/export', [DocumentTemplateController::class, 'export'])->name('export');
        Route::post('/import', [DocumentTemplateController::class, 'import'])->name('import');

        // CRUD classique
        Route::get('/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('show');
        Route::get('/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->name('edit');
        Route::put('/{documentTemplate}', [DocumentTemplateController::class, 'update'])->name('update');
        Route::delete('/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('destroy');

        // Édition du code source du fichier Blade
        Route::get('/{documentTemplate}/edit-source', [DocumentTemplateController::class, 'editSource'])->name('edit-source');
        Route::put('/{documentTemplate}/edit-source', [DocumentTemplateController::class, 'updateSource'])->name('update-source');

        // Designer WYSIWYG (publipostage sans code)
        Route::get('/{documentTemplate}/designer', [DocumentTemplateController::class, 'designer'])->name('designer');
        Route::put('/{documentTemplate}/designer', [DocumentTemplateController::class, 'updateDesigner'])->name('update-designer');
        Route::post('/{documentTemplate}/designer/preview', [DocumentTemplateController::class, 'previewDesigner'])->name('preview-designer');

        // Actions sur template
        Route::post('/{documentTemplate}/duplicate', [DocumentTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{documentTemplate}/toggle-status', [DocumentTemplateController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{documentTemplate}/download', [DocumentTemplateController::class, 'download'])->name('download');
        Route::get('/{documentTemplate}/version-history', [DocumentTemplateController::class, 'versionHistory'])->name('version-history');
        Route::post('/{documentTemplate}/restore-version/{versionId}', [DocumentTemplateController::class, 'restoreVersion'])->name('restore-version');

        // Preview
        Route::get('/{documentTemplate}/preview', [DocumentTemplateController::class, 'preview'])->name('preview');
        Route::post('/{documentTemplate}/preview/html', [DocumentTemplateController::class, 'previewHtml'])->name('preview.html');
        Route::post('/{documentTemplate}/preview/pdf', [DocumentTemplateController::class, 'previewPdf'])->name('preview.pdf');

        // AJAX : Charger les workflow steps selon organisation/opération
        Route::get('/ajax/workflow-steps', [DocumentTemplateController::class, 'getWorkflowSteps'])->name('ajax.workflow-steps');
    });

    /*
    |--------------------------------------------------------------------------
    | 📋 GESTION DES DOCUMENTS GÉNÉRÉS (16 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {

        // Liste et historique des documents générés
        Route::get('/', [GeneratedDocumentController::class, 'index'])->name('index');

        // Formulaire de génération manuelle
        Route::get('/create', [GeneratedDocumentController::class, 'create'])->name('create');

        // Créer/Générer un document
        Route::post('/', [GeneratedDocumentController::class, 'store'])->name('store');
        Route::post('/generate', [GeneratedDocumentController::class, 'generate'])->name('generate');

        // Voir les détails d'un document généré
        Route::get('/{generation}', [GeneratedDocumentController::class, 'show'])->name('show');

        // Télécharger un document
        Route::get('/{generation}/download', [GeneratedDocumentController::class, 'download'])->name('download');

        // Régénérer un document
        Route::post('/{generation}/regenerate', [GeneratedDocumentController::class, 'regenerate'])->name('regenerate');

        // Invalider un document
        Route::put('/{generation}/invalidate', [GeneratedDocumentController::class, 'invalidate'])->name('invalidate');

        // Réactiver un document invalidé
        Route::put('/{generation}/reactivate', [GeneratedDocumentController::class, 'reactivate'])->name('reactivate');

        // Supprimer un document
        Route::delete('/{generation}', [GeneratedDocumentController::class, 'destroy'])->name('destroy');

        // Actions groupées
        Route::post('/bulk-download', [GeneratedDocumentController::class, 'bulkDownload'])->name('bulk-download');
        Route::post('/bulk-invalidate', [GeneratedDocumentController::class, 'bulkInvalidate'])->name('bulk-invalidate');
        Route::post('/bulk-delete', [GeneratedDocumentController::class, 'bulkDelete'])->name('bulk-delete');

        // Export CSV des documents
        Route::get('/export/csv', [GeneratedDocumentController::class, 'export'])->name('export');

        // AJAX : Charger les templates pour une organisation
        Route::get('/ajax/templates-for-organisation', [GeneratedDocumentController::class, 'getTemplatesForOrganisation'])
            ->name('ajax.templates-for-organisation');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔍 GESTION DES VÉRIFICATIONS (Admin uniquement - 3 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-verifications')->name('document-verifications.')->group(function () {

        // Liste de toutes les vérifications (Admin)
        Route::get('/', [PublicDocVerificationController::class, 'adminIndex'])
            ->name('index');

        // Historique des vérifications d'un document spécifique (Admin)
        Route::get('/{generation}/verifications', [PublicDocVerificationController::class, 'documentVerifications'])
            ->name('history');

        // Export CSV des vérifications (Admin)
        Route::get('/export/verifications', [PublicDocVerificationController::class, 'exportVerifications'])
            ->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | ✅ VALIDATION ENTITIES - Gestion des entités de validation (8 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('validation-entities')->name('validation-entities.')->group(function () {
        Route::get('/', [ValidationEntityController::class, 'index'])->name('index');
        Route::get('/create', [ValidationEntityController::class, 'create'])->name('create');
        Route::post('/', [ValidationEntityController::class, 'store'])->name('store');
        Route::get('/{id}', [ValidationEntityController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ValidationEntityController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ValidationEntityController::class, 'update'])->name('update');
        Route::delete('/{id}', [ValidationEntityController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ValidationEntityController::class, 'toggleStatus'])->name('toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | ⚙️ MODULE WORKFLOW STEPS - GESTION DES ÉTAPES DE WORKFLOW (19 routes)
    |--------------------------------------------------------------------------
    | Gestion complète des étapes du workflow de validation
    | ✅ Ajouté le : 02/11/2025
    | ✅ 19 routes (7 CRUD + 12 custom)
    | 
    | Fonctionnalités :
    | - CRUD complet des étapes
    | - Timeline visuelle avec ordre
    | - Drag & drop pour réorganisation
    | - Statistiques avancées
    | - Duplication d'étapes
    | - Export de configuration
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow-steps')->name('workflow-steps.')->group(function () {

        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========

        Route::get('/', [WorkflowStepController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowStepController::class, 'create'])->name('create');

        // ⭐ ROUTES DE CONFIGURATION (AVANT /{id}) ⭐
        Route::get('/configure', [WorkflowStepController::class, 'configure'])->name('configure');
        Route::post('/configure/save', [WorkflowStepController::class, 'saveConfiguration'])->name('configure.save');
        Route::get('/timeline', [WorkflowStepController::class, 'timeline'])->name('timeline');

        // Routes AJAX
        Route::post('/{stepId}/assign-entity', [WorkflowStepController::class, 'assignEntity'])->name('assign-entity');
        Route::delete('/{stepId}/remove-entity/{entityId}', [WorkflowStepController::class, 'removeEntity'])->name('remove-entity');
        Route::post('/{stepId}/reorder-entities', [WorkflowStepController::class, 'reorderEntities'])->name('reorder-entities');

        // Toggle, reorder, duplicate, export, statistics
        Route::patch('/{id}/toggle-status', [WorkflowStepController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/reorder', [WorkflowStepController::class, 'reorder'])->name('reorder');
        Route::post('/{id}/duplicate', [WorkflowStepController::class, 'duplicate'])->name('duplicate');
        Route::get('/export', [WorkflowStepController::class, 'export'])->name('export');
        Route::get('/{id}/statistics', [WorkflowStepController::class, 'statistics'])->name('statistics');

        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========

        Route::get('/{id}', [WorkflowStepController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkflowStepController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkflowStepController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkflowStepController::class, 'destroy'])->name('destroy');
        Route::post('/', [WorkflowStepController::class, 'store'])->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | 🗺️ GESTION GÉOLOCALISATION - ROUTES CRUD
    |--------------------------------------------------------------------------
    */
    Route::prefix('geolocalisation')->name('geolocalisation.')->group(function () {
        Route::prefix('provinces')->name('provinces.')->group(function () {
            Route::get('/', [GeographyController::class, 'provinces'])->name('index');
            Route::get('/create', [GeographyController::class, 'createProvince'])->name('create');
            Route::post('/', [GeographyController::class, 'storeProvince'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showProvince'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editProvince'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateProvince'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteProvince'])->name('destroy');
            Route::patch('/{id}/toggle-status', [GeographyController::class, 'toggleStatusProvince'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionProvince'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportProvinces'])->name('export');
        });

        Route::prefix('departements')->name('departements.')->group(function () {
            Route::get('/', [GeographyController::class, 'departements'])->name('index');
            Route::get('/create', [GeographyController::class, 'createDepartement'])->name('create');
            Route::post('/', [GeographyController::class, 'storeDepartement'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showDepartement'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editDepartement'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateDepartement'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteDepartement'])->name('destroy');
            Route::patch('/{id}/toggle-status', [GeographyController::class, 'toggleStatusDepartement'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionDepartement'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportDepartements'])->name('export');
        });

        Route::prefix('communes-villes')->name('communes-villes.')->group(function () {
            Route::get('/', [GeographyController::class, 'communesVilles'])->name('index');
            Route::get('/create', [GeographyController::class, 'createCommuneVille'])->name('create');
            Route::post('/', [GeographyController::class, 'storeCommuneVille'])->name('store');
            Route::get('/by-departement/{departementId}', [GeographyController::class, 'getCommunesByDepartement'])->name('by-departement');
            Route::get('/{id}', [GeographyController::class, 'showCommuneVille'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editCommuneVille'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateCommuneVille'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteCommuneVille'])->name('destroy');
            Route::patch('/{id}/toggle-status', [GeographyController::class, 'toggleStatusCommuneVille'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionCommuneVille'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportCommunesVilles'])->name('export');
        });


        Route::prefix('arrondissements')->name('arrondissements.')->group(function () {
            Route::get('/', [GeographyController::class, 'arrondissements'])->name('index');
            Route::get('/create', [GeographyController::class, 'createArrondissement'])->name('create');
            Route::post('/', [GeographyController::class, 'storeArrondissement'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showArrondissement'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editArrondissement'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateArrondissement'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteArrondissement'])->name('destroy');
            Route::post('/{id}/toggle-status', [GeographyController::class, 'toggleStatusArrondissement'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionArrondissement'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportArrondissements'])->name('export');
        });

        Route::prefix('cantons')->name('cantons.')->group(function () {
            Route::get('/', [GeographyController::class, 'cantons'])->name('index');
            Route::get('/create', [GeographyController::class, 'createCanton'])->name('create');
            Route::post('/', [GeographyController::class, 'storeCanton'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showCanton'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editCanton'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateCanton'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteCanton'])->name('destroy');
            Route::post('/{id}/toggle-status', [GeographyController::class, 'toggleStatusCanton'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionCanton'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportCantons'])->name('export');
        });

        Route::prefix('regroupements')->name('regroupements.')->group(function () {
            Route::get('/', [GeographyController::class, 'regroupements'])->name('index');
            Route::get('/create', [GeographyController::class, 'createRegroupement'])->name('create');
            Route::post('/', [GeographyController::class, 'storeRegroupement'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showRegroupement'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editRegroupement'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateRegroupement'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteRegroupement'])->name('destroy');
            Route::post('/{id}/toggle-status', [GeographyController::class, 'toggleStatusRegroupement'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionRegroupement'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportRegroupements'])->name('export');
        });

        Route::prefix('localites')->name('localites.')->group(function () {
            Route::get('/', [GeographyController::class, 'localites'])->name('index');
            Route::get('/create', [GeographyController::class, 'createLocalite'])->name('create');
            Route::post('/', [GeographyController::class, 'storeLocalite'])->name('store');
            Route::get('/{id}', [GeographyController::class, 'showLocalite'])->name('show');
            Route::get('/{id}/edit', [GeographyController::class, 'editLocalite'])->name('edit');
            Route::put('/{id}', [GeographyController::class, 'updateLocalite'])->name('update');
            Route::delete('/{id}', [GeographyController::class, 'deleteLocalite'])->name('destroy');
            Route::post('/{id}/toggle-status', [GeographyController::class, 'toggleStatusLocalite'])->name('toggle-status');
            Route::post('/bulk-action', [GeographyController::class, 'bulkActionLocalite'])->name('bulk-action');
            Route::get('/export', [GeographyController::class, 'exportLocalites'])->name('export');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 🗺️ API GÉOLOCALISATION - ROUTES AJAX EN CASCADE
    |--------------------------------------------------------------------------
    | Routes AJAX pour chargement dynamique des données géographiques
    | ✅ Mis à jour le : 25/11/2025
    | ✅ 10 routes pour cascade géolocalisation complète
    | 
    | Hiérarchie :
    | - Zone Urbaine : Province > Département > Commune > Arrondissement > Quartier
    | - Zone Rurale : Province > Département > Canton > Regroupement > Village
    | 
    | Utilisation : Formulaire création organisation (Admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api/geo')->name('api.geo.')->group(function () {

        // Provinces (point de départ)
        Route::get('/provinces', [DossierController::class, 'getProvinces'])
            ->name('provinces');

        // Départements d'une province
        Route::get('/departements/{province_id}', [DossierController::class, 'getDepartements'])
            ->name('departements');

        // ZONE URBAINE : Communes d'un département
        Route::get('/communes/{departement_id}', [DossierController::class, 'getCommunes'])
            ->name('communes');

        // ZONE URBAINE : Arrondissements d'une commune
        Route::get('/arrondissements/{commune_id}', [DossierController::class, 'getArrondissements'])
            ->name('arrondissements');

        // ZONE RURALE : Cantons d'un département
        Route::get('/cantons/{departement_id}', [DossierController::class, 'getCantons'])
            ->name('cantons');

        // ZONE RURALE : Regroupements d'un canton
        Route::get('/regroupements/{canton_id}', [DossierController::class, 'getRegroupements'])
            ->name('regroupements');

        // ZONE URBAINE : Quartiers d'un arrondissement
        Route::get('/quartiers/{arrondissement_id}', [DossierController::class, 'getQuartiers'])
            ->name('quartiers');

        // ZONE RURALE : Villages d'un regroupement
        Route::get('/villages/{regroupement_id}', [DossierController::class, 'getVillages'])
            ->name('villages');

        // Localités (villages ou quartiers)
        Route::get('/localites', [DossierController::class, 'getLocalitesNew'])
            ->name('localites');

        // Règles métier d'un type d'organisation (validation dynamique)
        Route::get('/organisation-types/{organisation_type_id}/rules', [DossierController::class, 'getOrganisationTypeRules'])
            ->name('organisation-type-rules');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔧 API FONCTIONS - ROUTES AJAX
    |--------------------------------------------------------------------------
    | Route pour chargement dynamique des fonctions (select, autocomplete)
    | ✅ Ajouté le : 24/11/2025
    |--------------------------------------------------------------------------
    */
    Route::get('/api/fonctions', [FonctionController::class, 'apiList'])->name('api.fonctions');

    /*
    |--------------------------------------------------------------------------
    | PORTAIL PUBLIC - CMS ADMINISTRATEUR
    |--------------------------------------------------------------------------
    | Gestion du contenu du portail usager (actualités, documents, FAQ, etc.)
    |--------------------------------------------------------------------------
    */
    Route::prefix('portail')->name('portail.')->group(function () {
        // Dashboard Portail
        Route::get('/', [\App\Http\Controllers\Admin\Portail\PortailDashboardController::class, 'index'])->name('dashboard');

        // Actualités
        Route::resource('actualites', \App\Http\Controllers\Admin\Portail\ActualiteAdminController::class);

        // Documents publics
        Route::resource('documents', \App\Http\Controllers\Admin\Portail\DocumentAdminController::class);

        // FAQ
        Route::resource('faqs', \App\Http\Controllers\Admin\Portail\FaqAdminController::class);

        // Guides
        Route::resource('guides', \App\Http\Controllers\Admin\Portail\GuideAdminController::class);

        // Événements / Calendrier
        Route::resource('evenements', \App\Http\Controllers\Admin\Portail\EvenementAdminController::class);

        // Paramètres de contenu (hero, about, contact, footer)
        Route::get('parametres', [\App\Http\Controllers\Admin\Portail\ParametreAdminController::class, 'index'])->name('parametres.index');
        Route::post('parametres', [\App\Http\Controllers\Admin\Portail\ParametreAdminController::class, 'update'])->name('parametres.update');
        Route::get('parametres/create', [\App\Http\Controllers\Admin\Portail\ParametreAdminController::class, 'create'])->name('parametres.create');
        Route::post('parametres/create', [\App\Http\Controllers\Admin\Portail\ParametreAdminController::class, 'store'])->name('parametres.store');
        Route::delete('parametres/{parametre}', [\App\Http\Controllers\Admin\Portail\ParametreAdminController::class, 'destroy'])->name('parametres.destroy');

        // Messages / Contact
        Route::get('messages', [\App\Http\Controllers\Admin\Portail\MessageAdminController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [\App\Http\Controllers\Admin\Portail\MessageAdminController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/repondre', [\App\Http\Controllers\Admin\Portail\MessageAdminController::class, 'repondre'])->name('messages.repondre');
        Route::post('messages/{message}/statut', [\App\Http\Controllers\Admin\Portail\MessageAdminController::class, 'updateStatut'])->name('messages.statut');
        Route::delete('messages/{message}', [\App\Http\Controllers\Admin\Portail\MessageAdminController::class, 'destroy'])->name('messages.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | API - GESTION DES TOKENS D'ACCÈS INTEROPÉRABILITÉ
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('tokens', [\App\Http\Controllers\Admin\ApiTokenController::class, 'index'])->name('tokens.index');
        Route::get('tokens/create', [\App\Http\Controllers\Admin\ApiTokenController::class, 'create'])->name('tokens.create');
        Route::post('tokens', [\App\Http\Controllers\Admin\ApiTokenController::class, 'store'])->name('tokens.store');
        Route::delete('tokens/{token}', [\App\Http\Controllers\Admin\ApiTokenController::class, 'destroy'])->name('tokens.destroy');
        Route::patch('tokens/{token}/activate', [\App\Http\Controllers\Admin\ApiTokenController::class, 'activate'])->name('tokens.activate');
    });

});
