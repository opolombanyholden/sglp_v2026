<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\GuideController;
use App\Http\Controllers\Operator\DossierController;
use App\Http\Controllers\Operator\AdherentController;
use App\Http\Controllers\Operator\DeclarationController;
use App\Http\Controllers\Operator\DocumentController;
use App\Http\Controllers\Operator\MessageController;
use App\Http\Controllers\Operator\OrganisationController;
use App\Http\Controllers\Operator\ProfileController;

/*
|--------------------------------------------------------------------------
| Routes OpÃ©rateurs - ComplÃ©mentaires Ã  web.php
|--------------------------------------------------------------------------
| Ces routes complÃ¨tent celles dÃ©finies dans web.php
| âš ï¸ LES ROUTES PRINCIPALES /operator/* SONT DANS web.php
| âš ï¸ NE PAS LES REDÃ‰FINIR ICI
|--------------------------------------------------------------------------
*/

Route::prefix('operator')->name('operator.')->middleware(['web', 'auth', 'verified', 'operator'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“‹ DOSSIERS - FONCTIONNALITÃ‰S AVANCÃ‰ES
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        // Gestion des brouillons
        Route::get('/brouillons', [DossierController::class, 'brouillons'])->name('brouillons.index');
        Route::post('/{dossier}/save-draft', [DossierController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{dossier}/restore-draft', [DossierController::class, 'restoreDraft'])->name('restore-draft');
        
        // Soumission et workflow (utilise les mÃ©thodes existantes)
        Route::post('/{dossier}/soumettre', [DossierController::class, 'soumettre'])->name('soumettre');
        Route::post('/{dossier}/retirer', [DossierController::class, 'retirer'])->name('retirer');
        Route::get('/{dossier}/historique', [DossierController::class, 'historique'])->name('historique');
        Route::get('/{dossier}/timeline', [DossierController::class, 'timeline'])->name('timeline');
        
        // Documents du dossier - NOMS DIFFÃ‰RENTS pour Ã©viter conflits avec web.php
        Route::post('/{dossier}/docs/upload', [DossierController::class, 'uploadDocument'])
            ->name('docs.upload');
        Route::delete('/{dossier}/docs/{document}', [DossierController::class, 'deleteDocument'])
            ->name('docs.delete');
        Route::post('/{dossier}/docs/{document}/replace', [DossierController::class, 'replaceDocument'])
            ->name('docs.replace');
        Route::get('/{dossier}/docs/{document}/download', [DossierController::class, 'downloadDocument'])
            ->name('docs.download');
        Route::get('/{dossier}/docs/{document}/preview', [DossierController::class, 'previewDocument'])
            ->name('docs.preview');
        
        // Commentaires et notes
        Route::post('/{dossier}/commentaires', [DossierController::class, 'addComment'])->name('commentaires.store');
        Route::put('/commentaires/{comment}', [DossierController::class, 'updateComment'])->name('commentaires.update');
        Route::delete('/commentaires/{comment}', [DossierController::class, 'deleteComment'])->name('commentaires.delete');
        
        // Gestion des anomalies - DÃ‰JÃ€ DÃ‰FINIES DANS web.php
        // Route::get('/anomalies', [DossierController::class, 'anomalies'])->name('anomalies');
        // Route::post('/anomalies/resolve/{adherent}', [DossierController::class, 'resolveAnomalie'])->name('anomalies.resolve');

        // Duplication et modÃ¨les
        Route::post('/{dossier}/duplicate', [DossierController::class, 'duplicate'])->name('duplicate');
        Route::post('/{dossier}/save-as-template', [DossierController::class, 'saveAsTemplate'])->name('save-template');
        Route::get('/templates', [DossierController::class, 'templates'])->name('templates');
        Route::post('/create-from-template/{template}', [DossierController::class, 'createFromTemplate'])
            ->name('create-from-template');

        // Templates et modÃ¨les
        Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/adherents-excel', [AdherentController::class, 'downloadTemplate'])->name('adherents-excel');
        Route::get('/adherents-csv', [AdherentController::class, 'downloadTemplate'])->name('adherents-csv');
        });

        

    });
    

    


    /*
    |--------------------------------------------------------------------------
    | ðŸ¢ ORGANISATIONS - GESTION AVANCÃ‰E ET NOUVEAUTÃ‰S âœ¨
    |--------------------------------------------------------------------------
    */
    Route::prefix('organisations')->name('organisations.')->group(function () {
        
        // =============================================
        // ðŸ†• GESTION PAR Ã‰TAPES - NOUVELLES ROUTES
        // =============================================
        
        // Gestion des brouillons par Ã©tapes
        Route::get('/drafts', [OrganisationController::class, 'listDrafts'])->name('drafts.list');
        Route::post('/draft/create', [OrganisationController::class, 'createDraft'])->name('draft.create');
        Route::get('/draft/{draftId}', [OrganisationController::class, 'getDraft'])->name('draft.get');
        Route::delete('/draft/{draftId}', [OrganisationController::class, 'deleteDraft'])->name('draft.delete');
        Route::get('/draft/{draftId}/resume', [OrganisationController::class, 'resumeDraft'])->name('draft.resume');
        
        // Sauvegarde et validation par Ã©tapes (AJAX)
        Route::post('/step/{step}/save', [OrganisationController::class, 'saveStep'])->name('step.save');
        Route::post('/step/{step}/validate', [OrganisationController::class, 'validateStep'])->name('step.validate');
        
        // Finalisation et crÃ©ation complÃ¨te
        Route::post('/draft/{draftId}/finalize', [OrganisationController::class, 'finalizeDraft'])->name('draft.finalize');
        
        // =============================================
        // GESTION AVANCÃ‰E EXISTANTE
        // =============================================
        
        // Gestion des membres dirigeants
        Route::prefix('/{organisation}/dirigeants')->name('dirigeants.')->group(function () {
            Route::get('/', [OrganisationController::class, 'dirigeants'])->name('index');
            Route::post('/', [OrganisationController::class, 'addDirigeant'])->name('store');
            Route::put('/{dirigeant}', [OrganisationController::class, 'updateDirigeant'])->name('update');
            Route::delete('/{dirigeant}', [OrganisationController::class, 'removeDirigeant'])->name('destroy');
            Route::post('/{dirigeant}/toggle-status', [OrganisationController::class, 'toggleDirigeantStatus'])
                ->name('toggle-status');
        });
        
        // Structures et sections
        Route::prefix('/{organisation}/sections')->name('sections.')->group(function () {
            Route::get('/', [OrganisationController::class, 'sections'])->name('index');
            Route::post('/', [OrganisationController::class, 'createSection'])->name('store');
            Route::put('/{section}', [OrganisationController::class, 'updateSection'])->name('update');
            Route::delete('/{section}', [OrganisationController::class, 'deleteSection'])->name('destroy');
            Route::get('/{section}/members', [OrganisationController::class, 'sectionMembers'])->name('members');
        });
        
        // ActivitÃ©s et Ã©vÃ©nements
        Route::prefix('/{organisation}/activites')->name('activites.')->group(function () {
            Route::get('/', [OrganisationController::class, 'activites'])->name('index');
            Route::post('/', [OrganisationController::class, 'createActivite'])->name('store');
            Route::put('/{activite}', [OrganisationController::class, 'updateActivite'])->name('update');
            Route::delete('/{activite}', [OrganisationController::class, 'deleteActivite'])->name('destroy');
            Route::post('/{activite}/participants', [OrganisationController::class, 'addParticipants'])
                ->name('participants.add');
        });
        
        // Finances et comptabilitÃ©
        Route::prefix('/{organisation}/finances')->name('finances.')->group(function () {
            Route::get('/', [OrganisationController::class, 'finances'])->name('index');
            Route::post('/recettes', [OrganisationController::class, 'addRecette'])->name('recettes.store');
            Route::post('/depenses', [OrganisationController::class, 'addDepense'])->name('depenses.store');
            Route::get('/bilan/{annee}', [OrganisationController::class, 'bilan'])->name('bilan');
            Route::get('/export/{annee}', [OrganisationController::class, 'exportFinances'])->name('export');
        });
        
        // Dissolution et transfert
        Route::post('/{organisation}/initiate-dissolution', [OrganisationController::class, 'initiateDissolution'])
            ->name('initiate-dissolution');
        Route::post('/{organisation}/transfer-members/{target}', [OrganisationController::class, 'transferMembers'])
            ->name('transfer-members');

        // âœ… PAGE DE CONFIRMATION SUPPRIMÃ‰E D'ICI - DÃ‰JÃ€ DANS web.php VIA DossierController
        // Route::get('/confirmation/{dossier}', [OrganisationController::class, 'confirmation'])->name('confirmation');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ‘¥ ADHÃ‰RENTS - GESTION AVANCÃ‰E (utilise les mÃ©thodes existantes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('members')->name('members.')->group(function () {
        // Recherche et filtrage avancÃ©s
        Route::get('/search', [AdherentController::class, 'search'])->name('search');
        Route::get('/filter', [AdherentController::class, 'filter'])->name('filter');
        Route::get('/inactive', [AdherentController::class, 'inactive'])->name('inactive');
        Route::get('/pending', [AdherentController::class, 'pending'])->name('pending');
        
        // Gestion en lot
        Route::post('/bulk-update', [AdherentController::class, 'bulkUpdate'])->name('bulk-update');
        Route::post('/bulk-delete', [AdherentController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-activate', [AdherentController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('/bulk-export', [AdherentController::class, 'bulkExport'])->name('bulk-export');
        
        // Historique et activitÃ©s
        Route::get('/{adherent}/historique', [AdherentController::class, 'historique'])->name('historique');
        Route::get('/{adherent}/activites', [AdherentController::class, 'activites'])->name('activites');
        Route::post('/{adherent}/add-note', [AdherentController::class, 'addNote'])->name('add-note');
        
        // Cartes et documents
        Route::get('/{adherent}/carte', [AdherentController::class, 'generateCarte'])->name('carte');
        Route::get('/{adherent}/attestation', [AdherentController::class, 'generateAttestation'])->name('attestation');
        Route::post('/{adherent}/send-credentials', [AdherentController::class, 'sendCredentials'])->name('send-credentials');
        
        // Auto-inscription (utilise la mÃ©thode existante)
        Route::get('/register/{token}', [AdherentController::class, 'publicRegister'])->name('public-register');
        Route::post('/register/{token}', [AdherentController::class, 'storePublicRegister'])->name('public-register.store');
        
        // Fondateurs (utilise les mÃ©thodes existantes) - ROUTES DIFFÃ‰RENTES pour Ã©viter conflits
        Route::get('/fondateurs-list/{organisation}', [AdherentController::class, 'fondateurs'])->name('fondateurs.list');
        Route::post('/fondateurs-add/{organisation}', [AdherentController::class, 'addFondateur'])->name('fondateurs.add');
        
        // Exclusion et rÃ©activation (utilise les mÃ©thodes existantes)
        Route::post('/{adherent}/exclude/{organisation}', [AdherentController::class, 'exclude'])->name('exclude');
        Route::post('/{adherent}/reactivate/{organisation}', [AdherentController::class, 'reactivate'])->name('reactivate');
        
        // Doublons (utilise la mÃ©thode existante)
        Route::get('/duplicates/{organisation}', [AdherentController::class, 'duplicates'])->name('duplicates');
        
        // Liens d'inscription (utilise la mÃ©thode existante) - ROUTE DIFFÃ‰RENTE
        Route::post('/gen-link/{organisation}', [AdherentController::class, 'generateRegistrationLink'])
            ->name('gen-link');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“„ DÃ‰CLARATIONS ANNUELLES (utilise les mÃ©thodes existantes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('declarations')->name('declarations.')->group(function () {
        // Routes dÃ©jÃ  dÃ©finies dans DeclarationController existant
        Route::get('/{declaration}/edit', [DeclarationController::class, 'edit'])->name('edit');
        Route::put('/{declaration}', [DeclarationController::class, 'update'])->name('update');
        Route::delete('/{declaration}', [DeclarationController::class, 'destroy'])->name('destroy');
        
        // Documents de dÃ©claration (utilise les mÃ©thodes existantes) - NOMS DIFFÃ‰RENTS
        Route::post('/{declaration}/docs/upload', [DeclarationController::class, 'uploadDocument'])
            ->name('docs.upload');
        Route::delete('/{declaration}/docs/{document}', [DeclarationController::class, 'deleteDocument'])
            ->name('docs.delete');
        Route::get('/{declaration}/docs/{document}/download', [DeclarationController::class, 'downloadDocument'])
            ->name('docs.download');
        
        // Brouillons et modÃ¨les
        Route::post('/{declaration}/save-draft', [DeclarationController::class, 'saveDraft'])->name('save-draft');
        Route::get('/templates/{type}', [DeclarationController::class, 'getTemplate'])->name('template');
        Route::get('/{declaration}/pdf', [DeclarationController::class, 'generatePdf'])->name('pdf');
        
        // Historique et versions
        Route::get('/{declaration}/versions', [DeclarationController::class, 'versions'])->name('versions');
        Route::post('/{declaration}/restore/{version}', [DeclarationController::class, 'restoreVersion'])
            ->name('restore-version');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“Š RAPPORTS D'ACTIVITÃ‰ (utilise les mÃ©thodes existantes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('rapports')->name('rapports.')->group(function () {
        // Routes dÃ©jÃ  dÃ©finies dans DeclarationController existant
        Route::get('/{rapport}/edit', [DeclarationController::class, 'rapportEdit'])->name('edit');
        Route::put('/{rapport}', [DeclarationController::class, 'rapportUpdate'])->name('update');
        Route::post('/{rapport}/soumettre', [DeclarationController::class, 'rapportSoumettre'])->name('soumettre');
        
        // Sections du rapport
        Route::post('/{rapport}/sections', [DeclarationController::class, 'addSection'])->name('sections.store');
        Route::put('/sections/{section}', [DeclarationController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{section}', [DeclarationController::class, 'deleteSection'])->name('sections.destroy');
        
        // Export et partage
        Route::get('/{rapport}/export/pdf', [DeclarationController::class, 'rapportExportPdf'])->name('export.pdf');
        Route::get('/{rapport}/export/word', [DeclarationController::class, 'rapportExportWord'])->name('export.word');
        Route::post('/{rapport}/share', [DeclarationController::class, 'shareRapport'])->name('share');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📁 DOCUMENTS ET FICHIERS - GESTION COMPLÈTE
    |--------------------------------------------------------------------------
    | Routes pour la gestion des documents de l'opérateur
    | Utilise DocumentController dans app/Http/Controllers/Operator/
    | ✅ Ajouté le : 01/11/2025
    | ✅ Compatible avec le layout operator.blade.php
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        // Liste et affichage
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        
        // Upload et création
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::post('/create', [DocumentController::class, 'create'])->name('create');
        
        // Téléchargement et prévisualisation
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/preview', [DocumentController::class, 'preview'])->name('preview');
        
        // Gestion
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        
        // Actions en lot
        Route::post('/bulk-download', [DocumentController::class, 'bulkDownload'])->name('bulk-download');
        Route::post('/bulk-delete', [DocumentController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Recherche et filtrage
        Route::get('/search/results', [DocumentController::class, 'search'])->name('search');
        Route::get('/filter/by-type', [DocumentController::class, 'filterByType'])->name('filter-by-type');
        Route::get('/filter/by-organisation', [DocumentController::class, 'filterByOrganisation'])->name('filter-by-organisation');
        
        // Statistiques
        Route::get('/stats/storage', [DocumentController::class, 'storageStats'])->name('stats.storage');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📄 ALIAS POUR "FILES" - COMPATIBILITÉ
    |--------------------------------------------------------------------------
    | Alias pour supporter les anciennes références à "files" 
    | Redirige vers la section "documents"
    |--------------------------------------------------------------------------
    */
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', function() {
            return redirect()->route('operator.documents.index');
        })->name('index');
        
        Route::get('/{document}', function($document) {
            return redirect()->route('operator.documents.show', $document);
        })->name('show');
        
        Route::get('/{document}/download', function($document) {
            return redirect()->route('operator.documents.download', $document);
        })->name('download');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ’° DEMANDES DE SUBVENTION (utilise les mÃ©thodes existantes du DossierController)
    |--------------------------------------------------------------------------
    */
    Route::prefix('subventions')->name('subventions.')->group(function () {
        // Routes additionnelles (logique simple en attendant implÃ©mentation)
        Route::get('/{subvention}/edit', function ($subvention) {
            return redirect()->route('operator.grants.show', $subvention)
                ->with('info', 'Module Ã©dition subvention en cours de dÃ©veloppement');
        })->name('edit');
        
        Route::put('/{subvention}', function ($subvention) {
            return redirect()->route('operator.grants.show', $subvention)
                ->with('info', 'Module mise Ã  jour subvention en cours de dÃ©veloppement');
        })->name('update');
        
        Route::post('/{subvention}/soumettre', function ($subvention) {
            return redirect()->route('operator.grants.show', $subvention)
                ->with('info', 'Module soumission subvention en cours de dÃ©veloppement');
        })->name('soumettre');
        
        Route::delete('/{subvention}', function ($subvention) {
            return redirect()->route('operator.grants.index')
                ->with('info', 'Module suppression subvention en cours de dÃ©veloppement');
        })->name('destroy');
        
        // Documents justificatifs - NOMS DIFFÃ‰RENTS
        Route::post('/{subvention}/docs/upload', function ($subvention) {
            return response()->json(['message' => 'Module upload document subvention en cours de dÃ©veloppement']);
        })->name('docs.upload');
        
        Route::delete('/{subvention}/docs/{document}', function ($subvention, $document) {
            return response()->json(['message' => 'Module suppression document subvention en cours de dÃ©veloppement']);
        })->name('docs.delete');
        
        // Suivi et rapports d'utilisation
        Route::get('/{subvention}/suivi', function ($subvention) {
            return view('operator.subventions.suivi-placeholder', compact('subvention'));
        })->name('suivi');
        
        Route::post('/{subvention}/rapport-utilisation', function ($subvention) {
            return redirect()->back()->with('info', 'Module rapport utilisation en cours de dÃ©veloppement');
        })->name('rapport-utilisation');
        
        Route::get('/{subvention}/justificatifs', function ($subvention) {
            return view('operator.subventions.justificatifs-placeholder', compact('subvention'));
        })->name('justificatifs');
        
        // Types et programmes disponibles
        Route::get('/programmes', function () {
            return view('operator.subventions.programmes-placeholder');
        })->name('programmes');
        
        Route::get('/eligibilite/{programme}', function ($programme) {
            return response()->json(['eligible' => true, 'message' => 'VÃ©rification en cours de dÃ©veloppement']);
        })->name('eligibilite');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ’¬ MESSAGERIE ET COMMUNICATIONS - ROUTES AVANCÃ‰ES
    |--------------------------------------------------------------------------
    */
    Route::prefix('messages')->name('messages.')->group(function () {
        // Extensions
        Route::post('/{message}/archive', [MessageController::class, 'archive'])->name('archive');
        Route::post('/{message}/attachments', [MessageController::class, 'addAttachment'])->name('attachments.add');
        Route::get('/attachments/{attachment}/download', [MessageController::class, 'downloadAttachment'])
            ->name('attachments.download');
        
        // Dossiers et organisation
        Route::get('/folder/{folder}', [MessageController::class, 'folder'])->name('folder');
        Route::post('/folders', [MessageController::class, 'createFolder'])->name('folders.create');
        Route::post('/{message}/move/{folder}', [MessageController::class, 'moveToFolder'])->name('move');
        
        // Recherche
        Route::get('/search/results', [MessageController::class, 'search'])->name('search');
        Route::get('/search/advanced', [MessageController::class, 'advancedSearch'])->name('search.advanced');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ”” NOTIFICATIONS AVANCÃ‰ES
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Extensions
        Route::post('/{notification}/mark-read', [MessageController::class, 'markNotificationAsRead'])
            ->name('mark-read');
        Route::delete('/{notification}', [MessageController::class, 'deleteNotification'])->name('delete');
        
        // PrÃ©fÃ©rences de notification
        Route::get('/preferences', [MessageController::class, 'notificationPreferences'])->name('preferences');
        Route::post('/preferences', [MessageController::class, 'updateNotificationPreferences'])
            ->name('preferences.update');
        
        // Abonnements
        Route::post('/subscribe/{type}', [MessageController::class, 'subscribe'])->name('subscribe');
        Route::post('/unsubscribe/{type}', [MessageController::class, 'unsubscribe'])->name('unsubscribe');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“… CALENDRIER ET Ã‰CHÃ‰ANCES (routes simples temporaires)
    |--------------------------------------------------------------------------
    */
    Route::prefix('calendrier')->name('calendrier.')->group(function () {
        Route::get('/', function () {
            return view('operator.calendrier-placeholder');
        })->name('index');
        
        Route::get('/data', function () {
            return response()->json(['events' => []]);
        })->name('data');
        
        Route::post('/event', function () {
            return response()->json(['message' => 'Module calendrier en cours de dÃ©veloppement']);
        })->name('event.store');
        
        Route::get('/echeances', function () {
            return view('operator.echeances-placeholder');
        })->name('echeances');
        
        Route::get('/rappels', function () {
            return view('operator.rappels-placeholder');
        })->name('rappels');
        
        Route::get('/export/ical', function () {
            return response('', 200, ['Content-Type' => 'text/calendar']);
        })->name('export.ical');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“š DOCUMENTATION ET AIDE (utilise GuideController existant)
    |--------------------------------------------------------------------------
    */
    Route::prefix('help')->name('help.')->group(function () {
        // Guides (utilise les mÃ©thodes existantes)
        Route::get('/guides', [ProfileController::class, 'guides'])->name('guides');
        Route::get('/documents-types', [ProfileController::class, 'documentsTypes'])->name('documents-types');
        
        // Routes de guide spÃ©cifiques (utilise GuideController existant)
        Route::get('/guide/creation', [GuideController::class, 'creation'])->name('guide.creation');
        Route::get('/guide/modification/{organisation}', [GuideController::class, 'modification'])->name('guide.modification');
        Route::get('/guide/cessation/{organisation}', [GuideController::class, 'cessation'])->name('guide.cessation');
        Route::get('/guide/declaration/{organisation}', [GuideController::class, 'declaration'])->name('guide.declaration');
        
        // Support (routes temporaires)
        Route::get('/support', function () {
            return view('operator.support-placeholder');
        })->name('support');
        
        Route::post('/support/ticket', function () {
            return redirect()->back()->with('success', 'Module tickets support en cours de dÃ©veloppement');
        })->name('support.ticket');
        
        Route::get('/tutorials', function () {
            return view('operator.tutorials-placeholder');
        })->name('tutorials');
    });
});

/*
|--------------------------------------------------------------------------
| ðŸ”— Routes API Operator pour AJAX et widgets âœ¨
|--------------------------------------------------------------------------
*/
Route::prefix('api/operator')->name('api.operator.')->middleware(['auth', 'operator'])->group(function () {
    // Dashboard widgets (utilise ProfileController existant)
    Route::get('/dashboard/stats', [ProfileController::class, 'getDashboardStats'])->name('dashboard.stats');
    Route::get('/dashboard/recent-activity', [ProfileController::class, 'getRecentActivity'])->name('dashboard.activity');
    Route::get('/dashboard/notifications', [ProfileController::class, 'getDashboardNotifications'])
        ->name('dashboard.notifications');
    
    // Recherche universelle
    Route::get('/search', [ProfileController::class, 'universalSearch'])->name('search');
    Route::get('/search/suggestions', [ProfileController::class, 'searchSuggestions'])->name('search.suggestions');
    
    // =============================================
    // ðŸ†• VALIDATION TEMPS RÃ‰EL POUR GESTION PAR Ã‰TAPES
    // =============================================
    
    // Validation mÃ©tier temps rÃ©el
    Route::post('/validate/nip', [ProfileController::class, 'validateNip'])->name('validate.nip');
    Route::post('/validate/phone', [ProfileController::class, 'validatePhone'])->name('validate.phone');
    Route::post('/validate/organisation-name', [OrganisationController::class, 'validateName'])
        ->name('validate.organisation-name');
    
    // VÃ©rifications spÃ©cifiques aux Ã©tapes
    Route::post('/validate/step-data', [OrganisationController::class, 'validateStepData'])->name('validate.step-data');
    Route::post('/validate/members-conflicts', [OrganisationController::class, 'validateMembersConflicts'])
        ->name('validate.members-conflicts');
    Route::post('/validate/documents-completeness', [OrganisationController::class, 'validateDocumentsCompleteness'])
        ->name('validate.documents-completeness');
    
    // =============================================
    // AUTOCOMPLÃ‰TION ET ASSISTANCE
    // =============================================
    
    // AutocomplÃ©tion
    Route::get('/autocomplete/communes', [ProfileController::class, 'autocompleteCommunnes'])
        ->name('autocomplete.communes');
    Route::get('/autocomplete/activites', [OrganisationController::class, 'autocompleteActivites'])
        ->name('autocomplete.activites');
    Route::get('/autocomplete/professions', [OrganisationController::class, 'autocompleteProfessions'])
        ->name('autocomplete.professions');
    
    // =============================================
    // UPLOADS ET FICHIERS
    // =============================================
    
    // Uploads et fichiers (utilise DocumentController existant)
    Route::post('/upload/avatar', [ProfileController::class, 'uploadAvatar'])->name('upload.avatar');
    Route::post('/upload/document', [DocumentController::class, 'uploadDocument'])->name('upload.document');
    Route::post('/upload/bulk', [DocumentController::class, 'bulkUpload'])->name('upload.bulk');
    
    // Upload spÃ©cifique aux Ã©tapes
    Route::post('/upload/step-document', [OrganisationController::class, 'uploadStepDocument'])
        ->name('upload.step-document');
    Route::delete('/delete/step-document/{document}', [OrganisationController::class, 'deleteStepDocument'])
        ->name('delete.step-document');
    
    // =============================================
    // STATISTIQUES ET MONITORING
    // =============================================
    
    // Statistiques personnelles
    Route::get('/stats/organisations', [OrganisationController::class, 'getStats'])->name('stats.organisations');
    Route::get('/stats/dossiers', [DossierController::class, 'getStats'])->name('stats.dossiers');
    Route::get('/stats/adherents', [AdherentController::class, 'getStats'])->name('stats.adherents');
    Route::get('/stats/drafts', [OrganisationController::class, 'getDraftsStats'])->name('stats.drafts');
    
    // =============================================
    // VÃ‰RIFICATIONS SYSTÃˆME
    // =============================================
    
    // VÃ©rifications systÃ¨me
    Route::get('/check/limits', [ProfileController::class, 'checkLimits'])->name('check.limits');
    Route::get('/check/deadlines', function () {
        return response()->json(['deadlines' => []]);
    })->name('check.deadlines');
    Route::get('/check/documents', [DocumentController::class, 'checkRequiredDocuments'])
        ->name('check.documents');
    
    // VÃ©rifications spÃ©cifiques aux brouillons
    Route::get('/check/draft-expiration/{draftId}', [OrganisationController::class, 'checkDraftExpiration'])
        ->name('check.draft-expiration');
    Route::get('/check/step-completion/{draftId}', [OrganisationController::class, 'checkStepCompletion'])
        ->name('check.step-completion');
});