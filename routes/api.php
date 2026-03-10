<?php
// ========================================================================
// ROUTES API - SGLP/PNGDI
// ========================================================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\OrganisationController;
use App\Http\Controllers\Api\ChunkProcessorController;
use App\Http\Controllers\Api\V1\OrganisationApiController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController;

/*
|--------------------------------------------------------------------------
| 🔍 API PUBLIQUE - VÉRIFICATION DE DOCUMENTS (MODULE DOCUMENTS)
|--------------------------------------------------------------------------
| Routes API JSON pour vérifier l'authenticité des documents générés
| ✅ Ajouté le : 28/10/2025
| ✅ Rate limiting : 120 requêtes par minute par IP
| ✅ Format de réponse standardisé JSON
| ✅ Compatible intégrations tierces et applications mobiles
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->middleware(['throttle:120,1'])->group(function () {
    
    /**
     * Vérifier un document par token (GET)
     * GET /api/verify-document/{token}
     * 
     * Paramètres :
     * - token : Token de vérification du document
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "valid": true,
     *   "document": {...},
     *   "organisation": {...},
     *   "timestamp": "2025-10-28T10:30:00.000Z"
     * }
     */
    Route::get('/verify-document/{token}', [DocumentVerificationController::class, 'apiVerify'])
        ->name('verify-document-get');

    Route::post('/verify-document', [DocumentVerificationController::class, 'apiVerifyPost'])
        ->name('verify-document-post');

    Route::get('/document-stats', [DocumentVerificationController::class, 'apiStats'])
        ->name('document-stats');

    Route::post('/verify-qr', [DocumentVerificationController::class, 'apiVerifyQr'])
        ->name('verify-qr-mobile');

    Route::get('/document-info/{token}', [DocumentVerificationController::class, 'apiDocumentInfo'])
        ->name('document-info');
});

// ========================================
// ROUTES CHUNKING - AUTHENTIFICATION CORRIGÉE
// ========================================

Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    
    /**
     * Traitement des chunks d'adhérents
     * POST /api/organisations/process-chunk
     */
    Route::post('/organisations/process-chunk', [ChunkProcessorController::class, 'processChunk'])
        ->name('api.organisations.process-chunk');
    
    /**
     * Rafraîchissement du token CSRF
     * GET /api/csrf-refresh
     */
    Route::get('/csrf-refresh', [ChunkProcessorController::class, 'refreshCSRF'])
        ->name('api.csrf-refresh');
    
    /**
     * Statistiques de performance du chunking
     * GET /api/chunking/performance
     */
    Route::get('/chunking/performance', [ChunkProcessorController::class, 'getPerformanceStats'])
        ->name('api.chunking.performance');
    
});

// ========================================
// ROUTES ORGANISATIONS EXISTANTES
// ========================================

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/organisations/check-existing-members', [OrganisationController::class, 'checkExistingMembers']);
});

// ========================================
// GROUPE API v1 - MIDDLEWARE WEB + AUTH
// ========================================

Route::prefix('v1')->middleware(['web', 'auth'])->group(function () {
    
    /**
     * Vérification NIP gabonais
     */
    Route::post('verify-nip', function (Request $request) {
        $request->validate([
            'nip' => 'required|string|size:13'
        ]);
        
        $nip = $request->input('nip');
        
        $isValid = preg_match('/^\d{13}$/', $nip) && 
                  !preg_match('/^(\d)\1{12}$/', $nip) && 
                  !in_array($nip, ['1234567890123', '3210987654321']);
        
        return response()->json([
            'success' => $isValid,
            'valid' => $isValid,
            'message' => $isValid ? 'NIP valide' : 'NIP invalide'
        ]);
    });
    
    /**
     * Upload de document
     */
    Route::post('upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        
        $allowedTypes = ['piece_identite', 'statut', 'pv', 'reglement', 'photo', 'autre'];
        if (!in_array($documentType, $allowedTypes)) {
            return response()->json(['success' => false, 'message' => 'Type de document invalide'], 422);
        }
        $fileName = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/' . auth()->id(), $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => '/storage/' . $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'message' => 'Document uploadé avec succès'
        ]);
    });
    
});

// ============================================================
// API V1 — INTEROPÉRABILITÉ (authentification par clé API)
// ============================================================
// Authentification : Authorization: Bearer <token>
// Rate limit : défini par token (défaut 60 req/min)
// Toutes les réponses : JSON, données publiques uniquement
// ============================================================

Route::prefix('v1/public')
    ->name('api.v1.')
    ->middleware(['throttle:120,1', 'api.key'])
    ->group(function () {

    // Statistiques agrégées
    Route::get('stats', [OrganisationApiController::class, 'stats'])
        ->name('stats')
        ->middleware('api.key:stats');

    // Vérification récépissé (route avant /{id} pour éviter conflit)
    Route::get('organisations/verify/{code}', [OrganisationApiController::class, 'verify'])
        ->name('organisations.verify')
        ->middleware('api.key:verify')
        ->where('code', '[a-zA-Z0-9\-\_\/]+');

    // Liste et détail organisations
    Route::get('organisations', [OrganisationApiController::class, 'index'])
        ->name('organisations.index')
        ->middleware('api.key:organisations');

    Route::get('organisations/{id}', [OrganisationApiController::class, 'show'])
        ->name('organisations.show')
        ->middleware('api.key:organisations')
        ->where('id', '[0-9]+');
});

// Documentation API (publique, sans authentification)
Route::get('v1/documentation', function () {
    return view('api.v1.documentation');
})->name('api.v1.documentation');

// Spécification OpenAPI JSON (publique)
Route::get('v1/openapi.json', function () {
    return response()->json(require resource_path('api/openapi.php'));
})->name('api.v1.openapi');

// ========================================
// ROUTES PUBLIQUES - VÉRIFICATION QR CODE (EXISTANTE)
// ========================================

Route::get('verify-qr/{code}', function ($code) {
    // Logique de vérification QR code
    return response()->json([
        'success' => true,
        'valid' => true,
        'message' => 'Code QR valide (simulation)'
    ]);
});