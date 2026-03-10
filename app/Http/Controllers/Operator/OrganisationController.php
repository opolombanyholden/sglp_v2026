<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Organisation;
use App\Models\OrganizationDraft;
use App\Models\Dossier;
use App\Models\Adherent;
use App\Models\User;
use App\Services\OrganisationValidationService;
use App\Services\OrganisationStepService;
use App\Services\WorkflowService;
use App\Services\QRCodeService;
use Exception;
use Illuminate\Support\Facades\Validator;

class OrganisationController extends Controller
{
    protected $organisationValidationService;
    protected $workflowService;
    protected $qrCodeService;

    public function __construct(
        OrganisationValidationService $organisationValidationService,
        WorkflowService $workflowService,
        QRCodeService $qrCodeService
    ) {
        $this->organisationValidationService = $organisationValidationService;
        $this->workflowService = $workflowService;
        $this->qrCodeService = $qrCodeService;
    }

    // =============================================
    // NOUVELLES MÉTHODES POUR GESTION PAR ÉTAPES
    // =============================================

    /**
     * Sauvegarder une étape via AJAX
     * POST /operator/organisations/step/{step}/save
     */
    public function saveStep(Request $request, int $step)
    {
        try {
            $stepService = app(OrganisationStepService::class);

            $request->validate([
                'data' => 'required|array',
                'session_id' => 'nullable|string'
            ]);

            $result = $stepService->saveStep(
                $step,
                $request->input('data'),
                auth()->id(),
                $request->input('session_id', session()->getId())
            );

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Erreur sauvegarde étape via contrôleur', [
                'step' => $step,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valider une étape sans sauvegarder
     * POST /operator/organisations/step/{step}/validate
     */
    public function validateStep(Request $request, int $step)
    {
        try {
            $stepService = app(OrganisationStepService::class);

            $request->validate([
                'data' => 'required|array'
            ]);

            $result = $stepService->validateStep($step, $request->input('data'));

            return response()->json([
                'success' => $result['valid'],
                'valid' => $result['valid'],
                'errors' => $result['errors'],
                'step' => $step
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un brouillon existant
     * GET /operator/organisations/draft/{draftId}
     */
    public function getDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'draft' => $draft,
                'statistics' => $draft->getStatistics(),
                'steps_summary' => $draft->getStepsSummary(),
                'next_step' => $draft->getNextStep()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les brouillons de l'utilisateur connecté
     * GET /operator/organisations/drafts
     */
    public function listDrafts(Request $request)
    {
        try {
            $query = OrganizationDraft::where('user_id', auth()->id());

            // Filtres
            if ($request->has('type') && $request->input('type') !== 'all') {
                $query->byType($request->input('type'));
            }

            if ($request->boolean('active_only', false)) {
                $query->active();
            }

            $drafts = $query->orderBy('last_saved_at', 'desc')
                ->limit(20)
                ->get();

            $draftsWithStats = $drafts->map(function ($draft) {
                return [
                    'id' => $draft->id,
                    'organization_type' => $draft->organization_type,
                    'current_step' => $draft->current_step,
                    'completion_percentage' => $draft->completion_percentage,
                    'last_saved_at' => $draft->last_saved_at,
                    'expires_at' => $draft->expires_at,
                    'is_expired' => $draft->isExpired(),
                    'statistics' => $draft->getStatistics(),
                    'can_resume' => !$draft->isExpired()
                ];
            });

            return response()->json([
                'success' => true,
                'drafts' => $draftsWithStats,
                'count' => $drafts->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des brouillons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau brouillon
     * POST /operator/organisations/draft/create
     */
    public function createDraft(Request $request)
    {
        try {
            $request->validate([
                'organization_type' => 'nullable|in:association,ong,parti_politique,confession_religieuse',
                'session_id' => 'nullable|string'
            ]);

            // Vérifier les limites d'organisations
            $type = $request->input('organization_type');
            if ($type) {
                $canCreate = $this->checkOrganisationLimits($type);
                if (!$canCreate['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $canCreate['message']
                    ], 422);
                }
            }

            $draft = OrganizationDraft::create([
                'user_id' => auth()->id(),
                'organization_type' => $type,
                'session_id' => $request->input('session_id', session()->getId()),
                'form_data' => [],
                'current_step' => 1,
                'completion_percentage' => 0,
                'last_saved_at' => now(),
                'expires_at' => now()->addDays(7)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Brouillon créé avec succès',
                'draft' => $draft,
                'draft_id' => $draft->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un brouillon
     * DELETE /operator/organisations/draft/{draftId}
     */
    public function deleteDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brouillon non trouvé'
                ], 404);
            }

            $draft->delete();

            return response()->json([
                'success' => true,
                'message' => 'Brouillon supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliser un brouillon et créer l'organisation
     * POST /operator/organisations/draft/{draftId}/finalize
     */
    public function finalizeDraft(int $draftId)
    {
        try {
            $stepService = app(OrganisationStepService::class);

            $result = $stepService->finalizeOrganisation($draftId);

            if ($result['success']) {
                // Rediriger vers la page de confirmation
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'organisation_id' => $result['organisation_id'],
                    'redirect_url' => route('operator.organisations.show', $result['organisation_id'])
                ]);
            } else {
                return response()->json($result, 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reprendre un brouillon existant
     * GET /operator/organisations/draft/{draftId}/resume
     */
    public function resumeDraft(int $draftId)
    {
        try {
            $draft = OrganizationDraft::where('id', $draftId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$draft) {
                return redirect()->route('operator.organisations.index')
                    ->with('error', 'Brouillon non trouvé');
            }

            if ($draft->isExpired()) {
                return redirect()->route('operator.organisations.index')
                    ->with('warning', 'Ce brouillon a expiré');
            }

            // Étendre l'expiration automatiquement
            $draft->extendExpiration(7);

            // Rediriger vers la page de création avec le brouillon
            return redirect()->route('operator.organisations.create')
                ->with('resume_draft_id', $draft->id)
                ->with('success', 'Brouillon restauré avec succès');

        } catch (\Exception $e) {
            return redirect()->route('operator.organisations.index')
                ->with('error', 'Erreur lors de la reprise du brouillon');
        }
    }

    // =============================================
    // MÉTHODES EXISTANTES CONSERVÉES
    // =============================================

    /**
     * Afficher la liste des organisations de l'opérateur
     */
    public function index()
    {
        $organisations = Organisation::where('user_id', auth()->id())
            ->with(['dossier', 'adherents'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('operator.organisations.index', compact('organisations'));
    }

    /**
     * Afficher le formulaire de création d'une organisation
     * VERSION MISE À JOUR avec support des brouillons
     */
    public function create(Request $request, $type = null)
    {
        // Vérifier les limites d'organisations
        $canCreate = $this->checkOrganisationLimits($type);
        if (!$canCreate['success']) {
            return redirect()->route('operator.dashboard')
                ->with('error', $canCreate['message']);
        }

        // Vérifier s'il faut reprendre un brouillon
        $resumeDraftId = session('resume_draft_id');
        $existingDraft = null;

        if ($resumeDraftId) {
            $existingDraft = OrganizationDraft::where('id', $resumeDraftId)
                ->where('user_id', auth()->id())
                ->first();

            if ($existingDraft && !$existingDraft->isExpired()) {
                // Nettoyer la session
                session()->forget('resume_draft_id');
            } else {
                $existingDraft = null;
            }
        }

        // Si pas de brouillon existant, chercher les brouillons récents
        if (!$existingDraft) {
            $recentDrafts = OrganizationDraft::where('user_id', auth()->id())
                ->active()
                ->orderBy('last_saved_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $recentDrafts = collect();
        }

        $guides = $this->getGuideContent($type);
        $documentTypes = $this->getRequiredDocuments($type);
        $provinces = $this->getProvinces();

        return view('operator.organisations.create', compact(
            'type',
            'guides',
            'documentTypes',
            'provinces',
            'existingDraft',
            'recentDrafts'
        ));
    }

    //<!-- DÉBUT BLOC REMPLACEMENT store() -->
    /**
     * ✅ CORRECTION : Méthode store() avec imports corrigés
     */
    public function store(Request $request)
    {
        try {
            // Log de débogage
            Log::info('🚀 DÉBUT OrganisationController@store', [
                'user_id' => auth()->id(),
                'request_keys' => array_keys($request->all()),
                'csrf_token' => substr($request->input('_token'), 0, 10) . '...',
                'csrf_session' => substr(session()->token(), 0, 10) . '...',
                'csrf_match' => session()->token() === $request->input('_token'),
                'debug_mode' => $request->input('debug_mode')
            ]);

            // FORCE EXTENSION TIMEOUT pour gros volumes
            @set_time_limit(0);
            @ini_set('memory_limit', '1G');

            // ✅ ANALYSE AUTOMATIQUE DU VOLUME
            $adherentsData = $request->input('adherents', []);
            if (is_string($adherentsData)) {
                $adherentsArray = json_decode($adherentsData, true) ?: [];
            } else {
                $adherentsArray = is_array($adherentsData) ? $adherentsData : [];
            }

            $totalAdherents = count($adherentsArray);
            $volumeThreshold = 200; // Seuil pour déclenchement chunking automatique

            Log::info('📊 ANALYSE VOLUME SOUMISSION', [
                'user_id' => auth()->id(),
                'total_adherents' => $totalAdherents,
                'seuil_chunking' => $volumeThreshold,
                'method_detecte' => $totalAdherents >= $volumeThreshold ? 'INSERTION_DURING_CHUNKING' : 'STANDARD',
                'timestamp' => now()->toISOString()
            ]);

            // ✅ DÉCISION AUTOMATIQUE INTELLIGENTE
            if ($totalAdherents >= $volumeThreshold) {
                Log::info('🔄 REDIRECTION AUTOMATIQUE VERS INSERTION DURING CHUNKING', [
                    'total_adherents' => $totalAdherents,
                    'reason' => 'volume_necessitant_chunking',
                    'user_id' => auth()->id(),
                    'solution' => 'INSERTION_DURING_CHUNKING'
                ]);

                return $this->handleLargeVolumeSubmission($request, $adherentsArray);
            }

            // ✅ TRAITEMENT STANDARD pour petits volumes (CONSERVATION DU CODE EXISTANT)
            Log::info('📋 TRAITEMENT STANDARD', [
                'total_adherents' => $totalAdherents,
                'method' => 'insertion_monolithique_existante'
            ]);

            return $this->handleStandardSubmission($request);

        } catch (\Exception $e) {
            Log::error('❌ ERREUR OrganisationController@store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }
    //<!-- FIN BLOC REMPLACEMENT store() -->


    /**
     * ✅ CORRECTION COMPLÈTE : Gestion automatique gros volumes avec INSERTION DURING CHUNKING
     * Implémente la vraie solution "INSERTION DURING CHUNKING" 
     */
    private function handleLargeVolumeSubmission(Request $request, array $adherentsArray)
    {
        try {
            Log::info('🚀 DÉBUT CRÉATION ORGANISATION AVEC INSERTION DURING CHUNKING', [
                'total_adherents' => count($adherentsArray),
                'solution' => 'INSERTION_DURING_CHUNKING'
            ]);

            // PRÉPARER LES DONNÉES SANS LES ADHÉRENTS pour création rapide
            $organisationData = $request->except(['adherents']);
            $organisationData['phase_creation'] = 'organisation_sans_adherents';
            $organisationData['adherents_count_pending'] = count($adherentsArray);

            // ✅ S'assurer que les fondateurs sont transmis
            $allRequestData = $request->all();
            if (isset($allRequestData['fondateurs'])) {
                $organisationData['fondateurs'] = $allRequestData['fondateurs'];
                Log::info('✅ FONDATEURS AJOUTÉS À organisationData');
            } else {
                Log::error('❌ AUCUN FONDATEUR TROUVÉ DANS REQUEST');
            }

            // ✅ CRÉER L'ORGANISATION + DOSSIER (réutiliser logique existante)
            $result = $this->createOrganisationOnly($organisationData, $request);

            if (!$result['success']) {
                throw new \Exception('Échec création organisation: ' . ($result['message'] ?? 'Erreur inconnue'));
            }

            $organisation = $result['organisation'];
            $dossier = $result['dossier'];

            // ✅ SOLUTION OPTIMALE : INSERTION DURING CHUNKING IMMÉDIATE
            Log::info('🔄 DÉMARRAGE INSERTION DURING CHUNKING', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'total_adherents' => count($adherentsArray)
            ]);

            // ✅ APPEL DIRECT AU SYSTÈME DE CHUNKING AVEC INSERTION IMMÉDIATE
            $chunkingResult = $this->processWithInsertionDuringChunking($adherentsArray, $organisation, $dossier);

            if ($chunkingResult['success']) {
                // ✅ MISE À JOUR DU DOSSIER AVEC RÉSULTATS CHUNKING
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires ?? '{}', true);
                $donneesSupplementaires['insertion_during_chunking'] = [
                    'completed_at' => now()->toISOString(),
                    'total_inserted' => $chunkingResult['total_inserted'],
                    'method' => 'INSERTION_DURING_CHUNKING',
                    'chunks_processed' => $chunkingResult['chunks_processed'] ?? 0,
                    'errors' => $chunkingResult['errors'] ?? []
                ];

                $dossier->update([
                    'donnees_supplementaires' => json_encode($donneesSupplementaires, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now()
                ]);

                Log::info('✅ INSERTION DURING CHUNKING TERMINÉE AVEC SUCCÈS', [
                    'organisation_id' => $organisation->id,
                    'dossier_id' => $dossier->id,
                    'total_inserted' => $chunkingResult['total_inserted'],
                    'solution' => 'INSERTION_DURING_CHUNKING'
                ]);

                // ✅ REDIRECTION VERS CONFIRMATION AVEC DONNÉES CHUNKING
                // Vérifier le type de requête pour décider du type de réponse
                if ($request->ajax() || $request->expectsJson()) {
                    // Pour les requêtes AJAX : retourner JSON avec instruction de redirection
                    return response()->json([
                        'success' => true,
                        'message' => 'Adhérents traités avec succès par chunking',
                        'data' => [
                            'total_inserted' => $chunkingResult['total_inserted'],
                            'chunks_processed' => $chunkingResult['chunks_processed'] ?? 0,
                            'anomalies_count' => $chunkingResult['anomalies_count'] ?? 0,
                            'errors' => $chunkingResult['errors'] ?? [],
                            'dossier_id' => $dossier->id,
                            'organisation_id' => $organisation->id,
                            'numero_dossier' => $dossier->numero_dossier
                        ],
                        'solution' => 'INSERTION_DURING_CHUNKING',
                        'redirect_url' => route('operator.dossiers.confirmation', $dossier->id),
                        'should_redirect' => true,
                        'redirect_type' => 'confirmation',
                        'auto_redirect' => true,
                        'redirect_delay' => 2000
                    ]);
                } else {
                    // Pour les requêtes normales : redirection directe
                    return redirect()
                        ->route('operator.dossiers.confirmation', $dossier->id)
                        ->with('success', "Adhérents traités avec succès par chunking")
                        ->with('chunking_stats', [
                            'total_inserted' => $chunkingResult['total_inserted'],
                            'chunks_processed' => $chunkingResult['chunks_processed'] ?? 0,
                            'anomalies_count' => $chunkingResult['anomalies_count'] ?? 0,
                            'processing_time' => microtime(true) - LARAVEL_START
                        ]);
                }

            } else {
                // ✅ GESTION D'ERREUR CHUNKING
                Log::error('❌ ÉCHEC INSERTION DURING CHUNKING', [
                    'organisation_id' => $organisation->id,
                    'errors' => $chunkingResult['errors'] ?? [],
                    'total_inserted' => $chunkingResult['total_inserted'] ?? 0
                ]);

                throw new \Exception('Erreur lors de l\'insertion des adhérents: ' . implode(', ', $chunkingResult['errors'] ?? []));
            }

        } catch (\Exception $e) {
            Log::error('❌ ERREUR GESTION GROS VOLUME AVEC CHUNKING', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'adherents_count' => count($adherentsArray)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création avec INSERTION DURING CHUNKING: ' . $e->getMessage(),
                'error_code' => 'INSERTION_DURING_CHUNKING_FAILED',
                'solution' => 'INSERTION_DURING_CHUNKING'
            ], 500);
        }
    }

    /**
     * ✅ NOUVEAU : Créer seulement l'organisation (sans adhérents)
     */
    private function createOrganisationOnly(array $organisationData, Request $request)
    {
        try {
            DB::beginTransaction();

            // RÉUTILISER LA LOGIQUE EXISTANTE DE VALIDATION
            $validatedData = $this->validateOrganisationData($organisationData, $request);

            // ✅ CORRECTION: validateCompleteOrganisationData() retourne directement les données
            // PAS BESOIN de $validatedData['organisation'] - utiliser directement $validatedData

            // CRÉER L'ORGANISATION avec les bonnes clés
            $organisation = Organisation::create([
                'user_id' => auth()->id(),
                'type' => $validatedData['type_organisation'],
                'nom' => $validatedData['org_nom'],
                'sigle' => $validatedData['org_sigle'] ?? null,
                'objet' => $validatedData['org_objet'],
                'siege_social' => $validatedData['org_adresse_complete'],
                'province' => $validatedData['org_province'],
                'departement' => $validatedData['org_departement'] ?? null,
                'prefecture' => $validatedData['org_prefecture'],
                'zone_type' => $validatedData['org_zone_type'],
                'latitude' => $validatedData['org_latitude'] ?? null,
                'longitude' => $validatedData['org_longitude'] ?? null,
                'email' => $validatedData['org_email'] ?? null,
                'telephone' => $validatedData['org_telephone'],
                'site_web' => $validatedData['org_site_web'] ?? null,
                'date_creation' => $validatedData['org_date_creation'] ?? now(),
                'statut' => 'soumis',
                'nombre_adherents_min' => $this->getMinAdherents($validatedData['type_organisation'])
            ]);

            // Générer et assigner le numéro de récépissé
            $numeroRecepisse = $this->generateRecepisseNumber($validatedData['type_organisation']);
            $organisation->update(['numero_recepisse' => $numeroRecepisse]);

            // CRÉER LES FONDATEURS
            if (!empty($validatedData['fondateurs'])) {
                $this->createFondateurs($organisation, $validatedData['fondateurs']);
            }

            // CRÉER LES MEMBRES DU BUREAU
            if (!empty($validatedData['membresBureau'])) {
                $this->createMembresBureau($organisation, $validatedData['membresBureau']);
            }

            // CRÉER LE DOSSIER
            $donneesSupplementaires = [
                'demandeur' => [
                    'nip' => $validatedData['demandeur_nip'],
                    'nom' => $validatedData['demandeur_nom'],
                    'prenom' => $validatedData['demandeur_prenom'],
                    'email' => $validatedData['demandeur_email'],
                    'telephone' => $validatedData['demandeur_telephone'],
                    'role' => $validatedData['demandeur_role'] ?? 'Président'
                ],
                'guide_lu' => $validatedData['guide_read_confirm'] ?? true,
                'declarations' => [
                    'veracite' => $validatedData['declaration_veracite'] ?? true,
                    'conformite' => $validatedData['declaration_conformite'] ?? true,
                    'autorisation' => $validatedData['declaration_autorisation'] ?? true,
                    'exclusivite_parti' => $validatedData['declaration_exclusivite_parti'] ?? true
                ]
            ];

            $dossier = \App\Models\Dossier::create([
                'organisation_id' => $organisation->id,
                'numero_dossier' => $this->generateDossierNumber($validatedData['type_organisation']),
                'statut' => 'soumis',
                'soumis_le' => now(),
                'donnees_supplementaires' => json_encode($donneesSupplementaires),
                'user_id' => auth()->id()
            ]);

            DB::commit();

            \Log::info('✅ ORGANISATION CRÉÉE SANS ADHÉRENTS', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'numero_dossier' => $dossier->numero_dossier
            ]);

            return [
                'success' => true,
                'organisation' => $organisation, // ✅ Retourner l'objet organisation
                'dossier' => $dossier,
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'numero_dossier' => $dossier->numero_dossier
            ];

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('❌ ERREUR CRÉATION ORGANISATION SEULE', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * ✅ CONSERVATION : Traitement standard (code existant préservé)
     */
    private function handleStandardSubmission(Request $request)
    {
        // CONSERVER EXACTEMENT LE CODE EXISTANT DE LA MÉTHODE store() 
        // À partir de la validation jusqu'à la fin

        \Log::info('🔄 Début soumission organisation v3', [
            'user_id' => auth()->id(),
            'request_data_keys' => array_keys($request->all()),
            'type_organisation' => $request->input('type_organisation'),
            'fondateurs_type' => gettype($request->input('fondateurs', [])),
            'adherents_type' => gettype($request->input('adherents', []))
        ]);

        try {
            $totalInserted = 0;

            // Déterminer et normaliser le type d'organisation
            $type = $request->input('type_organisation', '');
            $typeMapping = [
                'association' => 'association',
                'ong' => 'ong',
                'parti_politique' => 'parti_politique',
                'parti' => 'parti_politique',
                'confession_religieuse' => 'confession_religieuse',
                'confession' => 'confession_religieuse',
            ];
            $type = $typeMapping[$type] ?? $type;

            // Limitation par utilisateur
            $canCreate = $this->checkOrganisationLimits($type);
            if (!$canCreate['success']) {
                \Log::warning('❌ Limite organisation atteinte - Standard', $canCreate);

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $canCreate['message'],
                        'errors' => ['limite' => $canCreate['message']]
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', $canCreate['message'])
                    ->withInput();
            }

            // Validation complète
            try {
                $validatedData = $this->validateCompleteOrganisationData($request, $type);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('❌ Erreur validation standard', [
                    'errors' => $e->errors(),
                    'user_id' => auth()->id(),
                    'type' => $type,
                ]);

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreurs de validation détectées',
                        'errors' => $e->errors()
                    ], 422);
                }

                throw $e;
            }

            \Log::info('Validation réussie', [
                'fondateurs_count' => count($validatedData['fondateurs'] ?? []),
                'adherents_count' => count($validatedData['adherents'] ?? []),
            ]);

            DB::beginTransaction();

            // ✅ CRÉATION ORGANISATION (même logique que storePhase1)
            $organisation = Organisation::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'nom' => $validatedData['org_nom'],
                'sigle' => $validatedData['org_sigle'] ?? null,
                'objet' => $validatedData['org_objet'],
                'siege_social' => $validatedData['org_adresse_complete'],
                'province' => $validatedData['org_province'],
                'departement' => $validatedData['org_departement'] ?? null,
                'prefecture' => $validatedData['org_prefecture'] ?? null,
                'zone_type' => $validatedData['org_zone_type'],
                'latitude' => $validatedData['org_latitude'] ?? null,
                'longitude' => $validatedData['org_longitude'] ?? null,
                'email' => $validatedData['org_email'] ?? null,
                'telephone' => $validatedData['org_telephone'],
                'site_web' => $validatedData['org_site_web'] ?? null,
                'date_creation' => $validatedData['org_date_creation'],
                'statut' => 'soumis',
                'nombre_adherents_min' => $this->getMinAdherents($type)
            ]);
            \Log::info('Organisation créée', ['organisation_id' => $organisation->id]);

            // Générer et assigner le numéro de récépissé
            $numeroRecepisse = $this->generateRecepisseNumber($type);
            $organisation->update(['numero_recepisse' => $numeroRecepisse]);

            // ✅ TRAITEMENT FONDATEURS
            if (!empty($validatedData['fondateurs'])) {
                $this->createFondateurs($organisation, $validatedData['fondateurs']);
                \Log::info('Fondateurs créés', ['count' => count($validatedData['fondateurs'])]);
            }

            // ✅ TRAITEMENT MEMBRES DU BUREAU
            if (!empty($validatedData['membresBureau'])) {
                $this->createMembresBureau($organisation, $validatedData['membresBureau']);
                \Log::info('Membres bureau créés', ['count' => count($validatedData['membresBureau'])]);
            }

            // ✅ TRAITEMENT ADHÉRENTS avec système d'anomalies
            if (!empty($validatedData['adherents'])) {
                $this->createAdherents($organisation, $validatedData['adherents']);
                $totalInserted = count($validatedData['adherents']);
                \Log::info('Adhérents créés', ['count' => $totalInserted]);
            }

            // ✅ CRÉATION DOSSIER
            $donneesSupplementaires = [
                'demandeur' => [
                    'nip' => $validatedData['demandeur_nip'],
                    'nom' => $validatedData['demandeur_nom'],
                    'prenom' => $validatedData['demandeur_prenom'],
                    'email' => $validatedData['demandeur_email'],
                    'telephone' => $validatedData['demandeur_telephone'],
                    'role' => $validatedData['demandeur_role'] ?? null
                ],
                'guide_lu' => $validatedData['guide_read_confirm'] ?? false,
                'declarations' => [
                    'veracite' => $validatedData['declaration_veracite'] ?? false,
                    'conformite' => $validatedData['declaration_conformite'] ?? false,
                    'autorisation' => $validatedData['declaration_autorisation'] ?? false
                ],
                'phase_creation' => 'complete',
                'completed_at' => now()->toISOString(),
            ];

            $donneesSupplementairesCleaned = $this->sanitizeJsonData($donneesSupplementaires);

            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'type_operation' => 'creation',
                'numero_dossier' => $this->generateDossierNumber($type),
                'statut' => 'soumis',
                'submitted_at' => now(),
                'donnees_supplementaires' => json_encode($donneesSupplementairesCleaned, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)
            ]);
            \Log::info('Dossier créé', ['dossier_id' => $dossier->id]);

            // ✅ TRAITEMENT DOCUMENTS
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($request, $dossier);
            }

            // Initialiser le workflow FIFO
            $this->workflowService->initializeWorkflow($dossier);

            // ✅ GÉNÉRATION QR CODE
            $qrCode = null;
            try {
                $qrCode = $this->qrCodeService->generateForDossier($dossier);
                if ($qrCode) {
                    \Log::info('QR Code généré avec succès', ['qr_code_id' => $qrCode->id]);
                }
            } catch (\Exception $e) {
                \Log::error('⚠️ Erreur QR Code non bloquante', [
                    'dossier_id' => $dossier->id,
                    'error' => $e->getMessage()
                ]);
                $qrCode = null;
            }

            DB::commit();
            \Log::info('Transaction validée avec succès', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'numero_recepisse' => $numeroRecepisse,
            ]);

            // Déterminer le mode de soumission
            $submissionMode = $request->input('submission_mode', 'traditional');
            $isPhase1 = in_array($submissionMode, ['phase1_only', '2_phases']) || $request->input('_phase') == 1;

            // Vérifier le type de requête pour décider du type de réponse
            if ($request->ajax() || $request->expectsJson()) {
                // Pour les requêtes AJAX : retourner JSON avec instruction de redirection
                $responseData = [
                    'success' => true,
                    'message' => 'Organisation créée avec succès',
                    'phase' => $isPhase1 ? 1 : 'complete',
                    'data' => [
                        'total_inserted' => $totalInserted,
                        'errors' => [],
                        'dossier_id' => $dossier->id,
                        'organisation_id' => $organisation->id,
                        'numero_recepisse' => $numeroRecepisse,
                        'next_phase_url' => $isPhase1 ? route('operator.dossiers.adherents-import', $dossier->id) : null,
                    ],
                    'solution' => 'STANDARD',
                    'redirect_url' => $isPhase1
                        ? route('operator.dossiers.adherents-import', $dossier->id)
                        : route('operator.dossiers.confirmation', $dossier->id),
                    'should_redirect' => true,
                    'redirect_type' => $isPhase1 ? 'phase2' : 'confirmation',
                    'auto_redirect' => true,
                    'redirect_delay' => 2000
                ];

                return response()->json($responseData);
            } else {
                // Pour les requêtes normales : redirection directe
                return redirect()
                    ->route('operator.dossiers.confirmation', $dossier->id)
                    ->with('success', 'Adhérents traités avec succès')
                    ->with('stats', [
                        'total_inserted' => $totalInserted
                    ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('❌ Erreur soumission organisation v3', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }


    // =============================================
    // NOUVELLES MÉTHODES POUR SOLUTION 2 PHASES
    // =============================================

    /**
     * CORRECTION MÉTHODE storePhase1() - VERSION CORRIGÉE
     * Résout le problème HTTP 422 "Type d'organisation non reconnu"
     * 
     * POST /operator/organisations/store-phase1
     */
    public function storePhase1(Request $request)
    {
        // FORCE EXTENSION TIMEOUT (même protection que store())
        @set_time_limit(0);
        @ini_set('memory_limit', '1G');

        // 🔍 DEBUGGING AMÉLIORÉ - Log toutes les données reçues
        \Log::info('🔍 DEBUGGING Phase 1 - Données reçues complètes', [
            'user_id' => auth()->id(),
            'all_request_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'raw_input' => $request->getContent(),
            'version' => 'phase1_debug_v2'
        ]);

        try {
            // 🔧 EXTRACTION TYPE ORGANISATION - MULTIPLE FALLBACKS
            $type = null;

            // Méthode 1: Clé standard
            if ($request->has('type_organisation')) {
                $type = $request->input('type_organisation');
                \Log::info('✅ Type trouvé via type_organisation', ['type' => $type]);
            }
            // Méthode 2: Clé alternative organizationType (JavaScript)
            elseif ($request->has('organizationType')) {
                $type = $request->input('organizationType');
                \Log::info('✅ Type trouvé via organizationType', ['type' => $type]);
            }
            // Méthode 3: Dans step1
            elseif ($request->has('step1.selectedOrgType')) {
                $type = $request->input('step1.selectedOrgType');
                \Log::info('✅ Type trouvé via step1.selectedOrgType', ['type' => $type]);
            }
            // Méthode 4: Dans metadata
            elseif ($request->has('metadata.selectedOrgType')) {
                $type = $request->input('metadata.selectedOrgType');
                \Log::info('✅ Type trouvé via metadata.selectedOrgType', ['type' => $type]);
            }
            // Méthode 5: Parsing des données nested
            else {
                $allData = $request->all();
                foreach ($allData as $key => $value) {
                    if (is_array($value) && isset($value['selectedOrgType'])) {
                        $type = $value['selectedOrgType'];
                        \Log::info('✅ Type trouvé via parsing nested', ['key' => $key, 'type' => $type]);
                        break;
                    }
                }
            }

            // 🚨 VALIDATION TYPE OBLIGATOIRE
            if (empty($type)) {
                \Log::error('❌ ERREUR Phase 1: Type organisation manquant', [
                    'received_keys' => array_keys($request->all()),
                    'search_attempts' => [
                        'type_organisation' => $request->has('type_organisation'),
                        'organizationType' => $request->has('organizationType'),
                        'step1.selectedOrgType' => $request->has('step1.selectedOrgType'),
                        'metadata.selectedOrgType' => $request->has('metadata.selectedOrgType')
                    ]
                ]);

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'organisation manquant dans les données reçues',
                        'errors' => ['type_organisation' => 'Type d\'organisation requis'],
                        'debug' => [
                            'received_keys' => array_keys($request->all()),
                            'help' => 'Vérifiez que organizationType ou type_organisation est envoyé'
                        ]
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', 'Type d\'organisation manquant')
                    ->withInput();
            }

            // 🔧 NORMALISATION TYPE
            $typeMapping = [
                'parti' => 'parti_politique',
                'parti_politique' => 'parti_politique',
                'confession' => 'confession_religieuse',
                'confession_religieuse' => 'confession_religieuse',
                'association' => 'association',
                'ong' => 'ong'
            ];

            $type = $typeMapping[$type] ?? $type;

            \Log::info('✅ Type normalisé pour Phase 1', [
                'type_final' => $type,
                'user_id' => auth()->id(),
                'phase' => 'CREATION_SANS_ADHERENTS'
            ]);

            // Vérifier les limites avant création (avec type valide)
            $canCreate = $this->checkOrganisationLimits($type);
            if (!$canCreate['success']) {
                \Log::warning('❌ Limite organisation atteinte - Phase 1', $canCreate);

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $canCreate['message'],
                        'errors' => ['limite' => $canCreate['message']]
                    ], 422);
                }

                return redirect()->back()
                    ->with('error', $canCreate['message'])
                    ->withInput();
            }

            // 🔧 VALIDATION PHASE 1 CORRIGÉE - Données flexibles
            try {
                $validatedData = $this->validatePhase1DataCorrected($request, $type);
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('❌ Erreur validation Phase 1 v2', [
                    'errors' => $e->errors(),
                    'user_id' => auth()->id(),
                    'type' => $type,
                    'phase' => 'VALIDATION_PHASE1'
                ]);

                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreurs de validation détectées - Phase 1',
                        'errors' => $e->errors()
                    ], 422);
                }

                throw $e;
            }

            \DB::beginTransaction();

            // ÉTAPE 1-4 : Créer l'organisation principale (IDENTIQUE à store())
            $organisation = Organisation::create([
                'user_id' => auth()->id(),
                'type' => $type,
                'nom' => $validatedData['org_nom'],
                'sigle' => $validatedData['org_sigle'] ?? null,
                'objet' => $validatedData['org_objet'],
                'siege_social' => $validatedData['org_adresse_complete'],
                'province' => $validatedData['org_province'],
                'departement' => $validatedData['org_departement'] ?? null,
                'prefecture' => $validatedData['org_prefecture'],
                'zone_type' => $validatedData['org_zone_type'],
                'latitude' => $validatedData['org_latitude'] ?? null,
                'longitude' => $validatedData['org_longitude'] ?? null,
                'email' => $validatedData['org_email'] ?? null,
                'telephone' => $validatedData['org_telephone'],
                'site_web' => $validatedData['org_site_web'] ?? null,
                'date_creation' => $validatedData['org_date_creation'],
                'statut' => 'soumis',
                'nombre_adherents_min' => $this->getMinAdherents($type)
            ]);

            \Log::info('✅ Organisation créée Phase 1 v2', ['organisation_id' => $organisation->id]);

            // Générer et assigner le numéro de récépissé
            $numeroRecepisse = $this->generateRecepisseNumber($type);
            $organisation->update(['numero_recepisse' => $numeroRecepisse]);

            // ÉTAPE 6 : Créer les fondateurs (IDENTIQUE à store())
            if (!empty($validatedData['fondateurs'])) {
                $this->createFondateurs($organisation, $validatedData['fondateurs']);
                \Log::info('✅ Fondateurs créés Phase 1 v2', ['count' => count($validatedData['fondateurs'])]);
            }

            // ÉTAPE 6b : Créer les membres du bureau
            if (!empty($validatedData['membresBureau'])) {
                $this->createMembresBureau($organisation, $validatedData['membresBureau']);
                \Log::info('✅ Membres bureau créés Phase 1 v2', ['count' => count($validatedData['membresBureau'])]);
            }

            // ÉTAPE 7 IGNORÉE EN PHASE 1 : PAS D'ADHÉRENTS
            \Log::info('ℹ️ Adhérents ignorés en Phase 1 - sera traité en Phase 2', [
                'adherents_received' => !empty($validatedData['adherents']) ? count($validatedData['adherents']) : 0
            ]);

            // ÉTAPE 5 : Créer le dossier de traitement (SANS adhérents)
            $donneesSupplementaires = [
                'demandeur' => [
                    'nip' => $validatedData['demandeur_nip'],
                    'nom' => $validatedData['demandeur_nom'],
                    'prenom' => $validatedData['demandeur_prenom'],
                    'email' => $validatedData['demandeur_email'],
                    'telephone' => $validatedData['demandeur_telephone'],
                    'role' => $validatedData['demandeur_role'] ?? null
                ],
                'guide_lu' => $validatedData['guide_read_confirm'] ?? false,
                'declarations' => [
                    'veracite' => $validatedData['declaration_veracite'] ?? false,
                    'conformite' => $validatedData['declaration_conformite'] ?? false,
                    'autorisation' => $validatedData['declaration_autorisation'] ?? false
                ],
                // MARQUEUR PHASE 1
                'phase_creation' => '1_sans_adherents',
                'phase1_completed_at' => now()->toISOString(),
                'adherents_phase2_pending' => !empty($validatedData['adherents'])
            ];

            // Nettoyer et encoder les données JSON (IDENTIQUE à store())
            $donneesSupplementairesCleaned = $this->sanitizeJsonData($donneesSupplementaires);

            $dossier = Dossier::create([
                'organisation_id' => $organisation->id,
                'type_operation' => 'creation',
                'numero_dossier' => $this->generateDossierNumber($type),
                'statut' => 'soumis',
                'submitted_at' => now(),
                'donnees_supplementaires' => json_encode($donneesSupplementairesCleaned, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION)
            ]);

            \Log::info('✅ Dossier créé Phase 1 v2', [
                'dossier_id' => $dossier->id,
                'phase' => 'CREATION_SANS_ADHERENTS'
            ]);

            // ÉTAPE 8 : Traiter les documents uploadés (IDENTIQUE à store())
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($request, $dossier);
            }

            // Initialiser le workflow FIFO (IDENTIQUE à store())
            $this->workflowService->initializeWorkflow($dossier);

            // Générer QR Code pour vérification (IDENTIQUE à store())
            $qrCode = null;
            try {
                $qrCode = $this->qrCodeService->generateForDossier($dossier);
                if ($qrCode) {
                    \Log::info('✅ QR Code généré avec succès Phase 1 v2', ['qr_code_id' => $qrCode->id]);
                }
            } catch (\Exception $e) {
                \Log::error('⚠️ Erreur QR Code non bloquante Phase 1 v2', [
                    'dossier_id' => $dossier->id,
                    'error' => $e->getMessage()
                ]);
                $qrCode = null;
            }

            // Générer accusé de réception pour Phase 1
            $accuseReceptionPath = $this->generateAccuseReceptionPhase1($dossier, $organisation, auth()->user());

            \DB::commit();

            \Log::info('🎉 Transaction Phase 1 validée avec succès v2', [
                'organisation_id' => $organisation->id,
                'dossier_id' => $dossier->id,
                'numero_recepisse' => $numeroRecepisse,
                'phase' => 'CREATION_SANS_ADHERENTS_COMPLETE'
            ]);

            // DONNÉES DE CONFIRMATION PHASE 1 SPÉCIFIQUES
            $confirmationData = [
                'organisation' => $organisation,
                'dossier' => $dossier,
                'numero_recepisse' => $numeroRecepisse,
                'qr_code' => $qrCode,
                'phase' => 1,
                'phase_message' => 'Phase 1 complétée avec succès : Organisation créée sans adhérents',
                'adherents_pending' => !empty($validatedData['adherents']),
                'next_phase_url' => route('operator.dossiers.adherents-import', $dossier->id),
                'accuse_reception_path' => $accuseReceptionPath,
                'message_confirmation' => 'Phase 1 terminée avec succès. Votre organisation a été créée. Pour ajouter les adhérents, procédez à la Phase 2.',
                'delai_traitement' => '72 heures ouvrées (après ajout des adhérents)'
            ];

            // ✅ CORRECTION : Logique de redirection corrigée
            $hasAdherents = !empty($validatedData['adherents']) && is_array($validatedData['adherents']) && count($validatedData['adherents']) > 0;

            // Sauvegarder adhérents en session AVANT la vérification
            if ($hasAdherents) {
                $this->saveAdherentsForPhase2($dossier->id, $validatedData['adherents']);
                \Log::info('✅ Adhérents sauvegardés pour Phase 2', [
                    'dossier_id' => $dossier->id,
                    'adherents_count' => count($validatedData['adherents'])
                ]);
            }

            // REDIRECTION CONDITIONNELLE CORRIGÉE
            if ($hasAdherents) {
                // PHASE 2 : Rediriger vers l'import des adhérents
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Phase 1 complétée avec succès : Organisation créée',
                        'phase' => 1,
                        'data' => [
                            'organisation_id' => $organisation->id,
                            'dossier_id' => $dossier->id,
                            'numero_recepisse' => $numeroRecepisse,
                            'next_phase_url' => route('operator.dossiers.adherents-import', $dossier->id),
                            'adherents_count' => count($validatedData['adherents'])
                        ],
                        'next_action' => 'PROCEED_TO_PHASE_2',
                        'redirect_to' => 'phase2'
                    ]);
                } else {
                    return redirect()->route('operator.dossiers.adherents-import', $dossier->id)
                        ->with('phase1_success', true)
                        ->with('adherents_count', count($validatedData['adherents']))
                        ->with('success', 'Phase 1 complétée. Procédez maintenant à l\'import des adhérents.');
                }
            } else {
                // FINALISATION DIRECTE : Pas d'adhérents à ajouter
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Organisation créée avec succès (sans adhérents)',
                        'phase' => 'complete',
                        'data' => [
                            'organisation_id' => $organisation->id,
                            'dossier_id' => $dossier->id,
                            'numero_recepisse' => $numeroRecepisse,
                            'next_phase_url' => route('operator.dossiers.adherents-import', $dossier->id)
                        ],
                        'next_action' => 'WORKFLOW_COMPLETE',
                        'redirect_to' => 'adherents-import'
                    ]);
                } else {
                    return redirect()->route('operator.dossiers.confirmation', $dossier->id)
                        ->with('success_data', $confirmationData);
                }
            }

        } catch (\Exception $e) {
            \DB::rollback();

            \Log::error('❌ Erreur création organisation Phase 1 v2', [
                'user_id' => auth()->id(),
                'type' => $type ?? 'unknown',
                'phase' => 'CREATION_SANS_ADHERENTS',
                'error_message' => $e->getMessage(),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'organisation - Phase 1',
                    'phase' => 1,
                    'error' => $e->getMessage(),
                    'debug' => config('app.debug') ? [
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ] : null
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'organisation (Phase 1). Veuillez réessayer.')
                ->withInput();
        }
    }



    /**
     * 🔧 NOUVELLE MÉTHODE : Validation Phase 1 CORRIGÉE - Gestion flexible des données
     */
    private function validatePhase1DataCorrected(Request $request, $type)
    {
        \Log::info('🔍 Validation Phase 1 v2 - Analyse des données', [
            'keys' => array_keys($request->all()),
            'type' => $type,
            'version' => 'phase1_validation_flexible_v2'
        ]);

        // 🔧 EXTRACTION FLEXIBLE DES DONNÉES
        $extractedData = $this->extractFormDataFlexible($request);

        // ✅ NORMALISATION DES NOMS DE CHAMPS (même logique que validateCompleteOrganisationData)
        // 1. org_adresse → org_adresse_complete
        if (isset($extractedData['org_adresse']) && !isset($extractedData['org_adresse_complete'])) {
            $extractedData['org_adresse_complete'] = $extractedData['org_adresse'];
        }

        // 2. org_province_id → org_province (résoudre le nom depuis l'ID)
        if (isset($extractedData['org_province_id']) && !isset($extractedData['org_province'])) {
            $provinceId = $extractedData['org_province_id'];
            $province = \App\Models\Province::find($provinceId);
            $extractedData['org_province'] = $province ? $province->nom : ($extractedData['province'] ?? '');
        }
        if (empty($extractedData['org_province']) && isset($extractedData['province'])) {
            $extractedData['org_province'] = $extractedData['province'];
        }

        // 2b. org_departement_id → org_departement (résoudre le nom depuis l'ID)
        if (isset($extractedData['org_departement_id']) && !isset($extractedData['org_departement'])) {
            $departementId = $extractedData['org_departement_id'];
            $departement = \App\Models\Departement::find($departementId);
            $extractedData['org_departement'] = $departement ? $departement->nom : '';
        }
        if (empty($extractedData['org_departement']) && isset($extractedData['departement'])) {
            $extractedData['org_departement'] = $extractedData['departement'];
        }

        // 3. zone_type → org_zone_type
        if (isset($extractedData['zone_type']) && !isset($extractedData['org_zone_type'])) {
            $extractedData['org_zone_type'] = $extractedData['zone_type'];
        }
        if (empty($extractedData['org_zone_type']) && isset($extractedData['zone_type_radio'])) {
            $extractedData['org_zone_type'] = $extractedData['zone_type_radio'];
        }

        // 4. Normalisation checkboxes : "true"/"false" (string) → "1"/"0"
        $checkboxFields = [
            'declaration_veracite', 'declaration_conformite', 'declaration_autorisation',
            'declaration_exclusivite_parti', 'guide_read_confirm',
        ];
        foreach ($checkboxFields as $field) {
            if (isset($extractedData[$field])) {
                $val = $extractedData[$field];
                if ($val === 'true' || $val === true || $val === 'on') {
                    $extractedData[$field] = '1';
                }
            }
        }

        // 5. Fondateurs : s'assurer que c'est un array depuis la request si non extrait
        if (empty($extractedData['fondateurs']) || !is_array($extractedData['fondateurs'])) {
            $fondateursFromRequest = $request->input('fondateurs', []);
            if (is_string($fondateursFromRequest)) {
                $decoded = json_decode($fondateursFromRequest, true);
                $fondateursFromRequest = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
            }
            if (!empty($fondateursFromRequest) && is_array($fondateursFromRequest)) {
                $extractedData['fondateurs'] = $fondateursFromRequest;
            }
        }

        \Log::info('🔍 Données après normalisation Phase 1', [
            'has_org_adresse_complete' => isset($extractedData['org_adresse_complete']),
            'has_org_province' => isset($extractedData['org_province']),
            'org_province_value' => $extractedData['org_province'] ?? 'ABSENT',
            'has_org_zone_type' => isset($extractedData['org_zone_type']),
            'fondateurs_count' => is_array($extractedData['fondateurs'] ?? null) ? count($extractedData['fondateurs']) : 0,
        ]);

        // 🔧 RÈGLES DE VALIDATION PHASE 1 ADAPTATIVES
        $rules = [
            // Type déjà validé plus haut
            'org_nom' => 'required|string|max:255',
            'org_objet' => 'required|string|min:10', // Plus souple
            'org_telephone' => 'required|string|max:255',
            'org_adresse_complete' => 'required|string|max:255',
            'org_province' => 'required|string|max:255',
            'org_prefecture' => 'nullable|string|max:255',
            'org_zone_type' => 'required|in:urbaine,rurale',

            // Demandeur - NOUVEAU FORMAT NIP
            'demandeur_nip' => [
                'required',
                'string',
                'regex:/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validateNipFormat($value)) {
                        $fail('Le format du NIP est invalide. Format attendu: XX-QQQQ-YYYYMMDD');
                    }
                }
            ],
            'demandeur_nom' => 'required|string|max:255',
            'demandeur_prenom' => 'required|string|max:255',
            'demandeur_email' => 'required|email|max:255',
            'demandeur_telephone' => 'required|string|max:20',

            // Fondateurs - validation souple
            'fondateurs' => 'nullable|array|min:1'
        ];



        $messages = [
            'org_nom.required' => 'Le nom de l\'organisation est obligatoire',
            'org_objet.required' => 'L\'objet de l\'organisation est obligatoire',
            'org_objet.min' => 'L\'objet doit contenir au moins 10 caractères',
            'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire',
            'demandeur_email.required' => 'L\'email du demandeur est obligatoire'
        ];

        // 🔧 VALIDATION AVEC DONNÉES EXTRAITES
        $validator = \Validator::make($extractedData, $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $validated = $validator->validated();

        // 🔧 COMPLÉTER AVEC VALEURS PAR DÉFAUT
        $validated['org_sigle'] = $extractedData['org_sigle'] ?? null;
        $validated['org_email'] = $extractedData['org_email'] ?? null;
        $validated['org_site_web'] = $extractedData['org_site_web'] ?? null;
        $validated['org_departement'] = $extractedData['org_departement'] ?? null;
        $validated['org_latitude'] = $extractedData['org_latitude'] ?? null;
        $validated['org_longitude'] = $extractedData['org_longitude'] ?? null;
        $validated['org_date_creation'] = $extractedData['org_date_creation'] ?? now()->format('Y-m-d');

        $validated['demandeur_role'] = $extractedData['demandeur_role'] ?? 'Président';
        $validated['guide_read_confirm'] = $extractedData['guide_read_confirm'] ?? true;
        $validated['declaration_veracite'] = $extractedData['declaration_veracite'] ?? true;
        $validated['declaration_conformite'] = $extractedData['declaration_conformite'] ?? true;
        $validated['declaration_autorisation'] = $extractedData['declaration_autorisation'] ?? true;

        // 🔧 TRAITEMENT FONDATEURS
        $validated['fondateurs'] = $extractedData['fondateurs'] ?? [];

        // 🔧 TRAITEMENT ADHÉRENTS (OPTIONNELS EN PHASE 1)
        $validated['adherents'] = $extractedData['adherents'] ?? [];

        \Log::info('✅ Validation Phase 1 v2 réussie', [
            'org_nom' => $validated['org_nom'],
            'fondateurs_count' => count($validated['fondateurs']),
            'adherents_count' => count($validated['adherents']),
            'type' => $type
        ]);

        return $validated;
    }

    /**
     * 🔧 MÉTHODE UTILITAIRE : Extraire les données de forme flexible
     */
    private function extractFormDataFlexible(Request $request)
    {
        $extracted = [];
        $allData = $request->all();

        // 🔍 STRATÉGIES D'EXTRACTION MULTIPLES

        // Stratégie 1: Données directes
        foreach ($allData as $key => $value) {
            if (strpos($key, 'org_') === 0 || strpos($key, 'demandeur_') === 0) {
                $extracted[$key] = $value;
            }
        }

        // Stratégie 2: Données dans des steps
        foreach (['step3', 'step4', 'step5', 'step6'] as $step) {
            if (isset($allData[$step]) && is_array($allData[$step])) {
                foreach ($allData[$step] as $key => $value) {
                    if (!isset($extracted[$key])) {
                        $extracted[$key] = $value;
                    }
                }
            }
        }

        // Stratégie 3: Parsing récursif
        $this->extractRecursive($allData, $extracted);

        \Log::info('🔍 Données extraites en Phase 1', [
            'extracted_keys' => array_keys($extracted),
            'strategies_used' => ['direct', 'steps', 'recursive']
        ]);

        return $extracted;
    }

    /**
     * 🔧 MÉTHODE UTILITAIRE : Extraction récursive
     */
    private function extractRecursive($data, &$extracted, $prefix = '')
    {
        if (!is_array($data))
            return;

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $this->extractRecursive($value, $extracted, $fullKey);
            } else {
                // Chercher les clés importantes
                if (preg_match('/^(org_|demandeur_|fondateurs|adherents|declaration_|guide_)/', $key)) {
                    if (!isset($extracted[$key])) {
                        $extracted[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * NOUVELLE MÉTHODE : Validation Phase 1 - Adhérents OPTIONNELS
     * Réutilise validateCompleteOrganisationData() en rendant adhérents optionnels
     */
    private function validatePhase1Data(Request $request, $type)
    {
        // Log des données reçues pour debugging
        \Log::info('Validation Phase 1 v1 - Adhérents optionnels', [
            'keys' => array_keys($request->all()),
            'type' => $type,
            'regle_metier' => 'Phase 1 sans adhérents obligatoires',
            'version' => 'phase1_validation_v1'
        ]);

        // RÈGLES IDENTIQUES À validateCompleteOrganisationData() SAUF ADHÉRENTS
        $rules = [
            // ÉTAPE 1 : Type
            'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse',

            // ÉTAPE 2 : Guide
            'guide_read_confirm' => 'sometimes|accepted',

            // ÉTAPE 3 : Demandeur - COLONNES CONFORMES À USERS TABLE
            // ÉTAPE 3 : Demandeur - NOUVEAU FORMAT NIP
            'demandeur_nip' => [
                'required',
                'string',
                'regex:/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validateNipFormat($value)) {
                        $fail('Le format du NIP est invalide. Format attendu: XX-QQQQ-YYYYMMDD');
                    }
                }
            ],
            'demandeur_nom' => 'required|string|max:255',
            'demandeur_prenom' => 'required|string|max:255',
            'demandeur_email' => 'required|email|max:255',
            'demandeur_telephone' => 'required|string|max:20',
            'demandeur_role' => 'nullable|string|max:255',
            'demandeur_civilite' => 'nullable|in:M,Mme,Mlle',
            'demandeur_date_naissance' => 'nullable|date|before:-18 years',
            'demandeur_nationalite' => 'nullable|string|max:255',
            'demandeur_adresse' => 'nullable|string|max:500',
            'demandeur_profession' => 'nullable|string|max:255',

            // ÉTAPE 4 : Organisation - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_nom' => 'required|string|max:255|unique:organisations,nom',
            'org_sigle' => 'nullable|string|max:255|unique:organisations,sigle',
            'org_objet' => 'required|string|min:50',
            'org_date_creation' => 'required|date',
            'org_telephone' => 'required|string|max:255',
            'org_email' => 'nullable|email|max:255',
            'org_site_web' => 'nullable|url|max:255',
            'org_domaine' => 'nullable|string|max:255',

            // ÉTAPE 5 : Coordonnées - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_adresse_complete' => 'required|string|max:255',
            'org_province' => 'required|string|max:255',
            'org_departement' => 'nullable|string|max:255',
            'org_prefecture' => 'nullable|string|max:255',
            'org_zone_type' => 'required|in:urbaine,rurale',
            'org_latitude' => 'nullable|numeric|between:-3.978,2.318',
            'org_longitude' => 'nullable|numeric|between:8.695,14.502',

            // ÉTAPE 6 : Fondateurs - VALIDATION IDENTIQUE (obligatoire)
            'fondateurs' => [
                'required',
                function ($attribute, $value, $fail) use ($type) {
                    /**
                     * ✅ ÉTAPE 3 : REMPLACER LE CONTENU DE LA FONCTION fondateurs
                     * 
                     * CHERCHER : 'fondateurs' => [ function ($attribute, $value, $fail) use ($type) {
                     * REMPLACER tout le contenu entre { et } par :
                     */

                    // ✅ DÉTECTER PHASE 1
                    $isPhase1 = request()->has('__phase_1_validation');

                    \Log::info('🔍 VALIDATION FONDATEURS', [
                        'is_phase_1' => $isPhase1,
                        'value_type' => gettype($value),
                        'value_count' => is_array($value) ? count($value) : 'not_array'
                    ]);

                    if (empty($value)) {
                        $fail('Au moins un fondateur est requis.');
                        return;
                    }

                    // Décoder JSON si nécessaire
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Les données des fondateurs sont invalides (JSON malformé): ' . json_last_error_msg());
                            return;
                        }
                        $value = $decoded;
                        request()->merge(['fondateurs' => $value]);
                    }

                    if (!is_array($value)) {
                        $fail('Les fondateurs doivent être un tableau.');
                        return;
                    }

                    $minRequired = $this->getMinFondateurs($type);
                    if (count($value) < $minRequired) {
                        $fail("Minimum {$minRequired} fondateurs requis pour ce type d'organisation.");
                    }

                    // ✅ VALIDATION ALLÉGÉE POUR PHASE 1
                    if ($isPhase1) {
                        \Log::info('✅ PHASE 1 : Validation allégée fondateurs');

                        foreach ($value as $index => $fondateur) {
                            if (!is_array($fondateur)) {
                                $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                                continue;
                            }

                            // Validation minimale pour Phase 1
                            if (empty($fondateur['nip'])) {
                                $fail("Le NIP du fondateur ligne " . ($index + 1) . " ne peut pas être vide.");
                            }
                            if (empty($fondateur['nom']) || empty($fondateur['prenom'])) {
                                $fail("Le nom et prénom du fondateur ligne " . ($index + 1) . " sont obligatoires.");
                            }

                            // ✅ VALEURS PAR DÉFAUT pour éviter erreurs
                            if (empty($fondateur['fonction'])) {
                                $value[$index]['fonction'] = 'Fondateur';
                                \Log::info('✅ Fonction par défaut assignée', ['ligne' => $index + 1]);
                            }
                            if (empty($fondateur['telephone'])) {
                                $value[$index]['telephone'] = 'A renseigner';
                                \Log::info('✅ Téléphone par défaut assigné', ['ligne' => $index + 1]);
                            }
                            if (empty($fondateur['profession'])) {
                                $value[$index]['profession'] = 'Dirigeant';
                                \Log::info('✅ Profession par défaut assignée', ['ligne' => $index + 1]);
                            }
                        }

                        request()->merge(['fondateurs' => $value]);
                        \Log::info('✅ FONDATEURS VALIDÉS POUR PHASE 1', ['count' => count($value)]);
                        return;
                    }

                    // Validation complète pour Phase 2
                    foreach ($value as $index => $fondateur) {
                        if (!is_array($fondateur)) {
                            $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }

                        if (empty($fondateur['nip'])) {
                            $fail("Le NIP du fondateur ligne " . ($index + 1) . " ne peut pas être vide.");
                        }

                        if (empty($fondateur['nom']) || empty($fondateur['prenom'])) {
                            $fail("Le nom et prénom du fondateur ligne " . ($index + 1) . " sont obligatoires.");
                        }
                        if (empty($fondateur['fonction'])) {
                            $fail("La fonction du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                        if (empty($fondateur['telephone'])) {
                            $fail("Le téléphone du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                    }


                }
            ],

            // ÉTAPE 7 : Adhérents OPTIONNELS EN PHASE 1
            'adherents' => [
                'nullable', // CHANGEMENT MAJEUR : nullable au lieu de required
                function ($attribute, $value, $fail) use ($type) {
                    // Si adhérents fournis, validation légère pour stockage temporaire
                    if (!empty($value)) {
                        // Décoder JSON si c'est une string
                        if (is_string($value)) {
                            $decoded = json_decode($value, true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                $fail('Les données des adhérents sont invalides (JSON malformé): ' . json_last_error_msg());
                                return;
                            }
                            $value = $decoded;
                            request()->merge(['adherents' => $value]);
                        }

                        if (!is_array($value)) {
                            $fail('Les adhérents doivent être un tableau.');
                            return;
                        }

                        // VALIDATION MINIMALE EN PHASE 1 (stockage temporaire)
                        foreach ($value as $index => $adherent) {
                            if (!is_array($adherent)) {
                                $fail("L'adhérent ligne " . ($index + 1) . " doit être un objet valide.");
                                continue;
                            }

                            // Vérifications de base seulement
                            if (empty($adherent['nip']) || trim($adherent['nip']) === '') {
                                $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " ne peut pas être vide.");
                            }

                            if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                                $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                            }
                        }
                    }
                }
            ],

            // ÉTAPE 9 : Déclarations finales
            'declaration_veracite' => 'sometimes|accepted',
            'declaration_conformite' => 'sometimes|accepted',
            'declaration_autorisation' => 'sometimes|accepted'
        ];

        // RÈGLES SPÉCIFIQUES PARTI POLITIQUE (sans minimum adhérents)
        if ($type === 'parti_politique') {
            $rules['declaration_exclusivite_parti'] = 'required|accepted';
            // PAS DE VALIDATION MINIMUM ADHÉRENTS EN PHASE 1
        }

        $messages = [
            'demandeur_nip.digits' => 'Le NIP du demandeur doit contenir exactement 14 caractere.',
            'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire.',
            'org_nom.unique' => 'Ce nom d\'organisation est déjà utilisé.',
            'org_sigle.unique' => 'Ce sigle est déjà utilisé.',
            'org_objet.min' => 'L\'objet de l\'organisation doit contenir au moins 50 caractères.',
            'org_objet.required' => 'L\'objet de l\'organisation est obligatoire.',
            'declaration_exclusivite_parti.required' => 'La déclaration d\'exclusivité pour parti politique est obligatoire.',
            'declaration_exclusivite_parti.accepted' => 'Vous devez accepter la déclaration d\'exclusivité.',
            'fondateurs.required' => 'Les fondateurs sont obligatoires même en Phase 1.',
            'adherents.nullable' => 'Les adhérents sont optionnels en Phase 1.',
            '*.accepted' => 'Cette déclaration est obligatoire.',
            '*.required' => 'Ce champ est obligatoire.'
        ];

        try {
            $validated = $request->validate($rules, $messages);

            // Post-traitement avec nettoyage des données (IDENTIQUE)
            if (isset($validated['fondateurs'])) {
                if (is_string($validated['fondateurs'])) {
                    $decoded = json_decode($validated['fondateurs'], true);
                    $validated['fondateurs'] = $decoded ?? [];
                }
                if (!is_array($validated['fondateurs'])) {
                    $validated['fondateurs'] = [];
                }

                // Nettoyer les NIP des fondateurs
                foreach ($validated['fondateurs'] as &$fondateur) {
                    if (isset($fondateur['nip'])) {
                        $fondateur['nip'] = $this->cleanNipForStorage($fondateur['nip']);
                    }
                }
            }

            // TRAITEMENT SPÉCIAL ADHÉRENTS PHASE 1
            if (isset($validated['adherents']) && !empty($validated['adherents'])) {
                if (is_string($validated['adherents'])) {
                    $decoded = json_decode($validated['adherents'], true);
                    $validated['adherents'] = $decoded ?? [];
                }
                if (!is_array($validated['adherents'])) {
                    $validated['adherents'] = [];
                }

                // Nettoyer les NIP des adhérents (stockage temporaire)
                foreach ($validated['adherents'] as &$adherent) {
                    if (isset($adherent['nip'])) {
                        $adherent['nip'] = $this->cleanNipForStorage($adherent['nip']);
                    }

                    // Assurer la fonction par défaut
                    if (empty($adherent['fonction'])) {
                        $adherent['fonction'] = 'Membre';
                    }
                }

                \Log::info('Adhérents reçus en Phase 1 pour stockage temporaire', [
                    'count' => count($validated['adherents']),
                    'note' => 'Seront traités en Phase 2 avec validation complète'
                ]);
            } else {
                // Pas d'adhérents fournis en Phase 1
                $validated['adherents'] = [];
                \Log::info('Aucun adhérent fourni en Phase 1 - Normal pour cette phase');
            }

            // Ajouter des valeurs par défaut (IDENTIQUE)
            $validated['org_departement'] = $request->input('org_departement');
            $validated['declaration_veracite'] = $request->has('declaration_veracite');
            $validated['declaration_conformite'] = $request->has('declaration_conformite');
            $validated['declaration_autorisation'] = $request->has('declaration_autorisation');
            $validated['guide_read_confirm'] = $request->has('guide_read_confirm');

            \Log::info('Validation Phase 1 réussie v1', [
                'fondateurs_count' => count($validated['fondateurs'] ?? []),
                'adherents_count' => count($validated['adherents'] ?? []),
                'type' => $type,
                'validation_version' => 'phase1_sans_adherents_obligatoires_v1'
            ]);

            return $validated;

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur validation Phase 1 v1', [
                'errors' => $e->errors(),
                'type' => $type,
                'validation_version' => 'phase1_sans_adherents_obligatoires_v1'
            ]);

            throw $e;
        }
    }

    /**
     * NOUVELLE MÉTHODE : Générer accusé de réception spécifique Phase 1
     * Indique que l'organisation est créée SANS adhérents
     */
    private function generateAccuseReceptionPhase1(Dossier $dossier, Organisation $organisation, $user)
    {
        try {
            $data = [
                'dossier' => $dossier,
                'organisation' => $organisation,
                'user' => $user,
                'date_generation' => now(),
                'numero_recepisse' => $organisation->numero_recepisse,
                'phase' => 1,
                'phase_message' => 'Phase 1 complétée : Organisation créée sans adhérents'
            ];

            $filename = 'accuse_reception_phase1_' . $dossier->numero_dossier . '_' . time() . '.pdf';
            $storagePath = 'accuses_reception/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);

            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $htmlContent = $this->generateAccuseReceptionPhase1HTML($data);
            file_put_contents($fullPath, $htmlContent);

            \App\Models\Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => 99,
                'nom_fichier' => $filename,
                'nom_original' => 'Accusé de réception Phase 1',
                'chemin_fichier' => $storagePath,
                'type_mime' => 'application/pdf',
                'taille' => strlen($htmlContent),
                'hash_fichier' => hash('sha256', $htmlContent),
                'is_system_generated' => true,
                'metadata' => json_encode(['phase' => 1, 'type' => 'accuse_phase1']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Log::info('Accusé Phase 1 généré avec succès v1', [
                'dossier_id' => $dossier->id,
                'filename' => $filename,
                'phase' => 1
            ]);

            return $storagePath;

        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé Phase 1 v1: ' . $e->getMessage(), [
                'dossier_id' => $dossier->id,
                'error' => $e->getTraceAsString()
            ]);
            return null;
        }
    }



    /**
     * NOUVELLE MÉTHODE : HTML pour accusé Phase 1
     */
    private function generateAccuseReceptionPhase1HTML($data)
    {
        $content = '
        <h2 style="text-align: center; color: #006633;">ACCUSÉ DE RÉCEPTION - PHASE 1</h2>
        
        <div class="phase-box" style="background: #e8f5e8; border: 2px solid #28a745; padding: 15px; margin: 20px 0;">
            <div class="phase-title" style="color: #28a745; font-size: 18px; font-weight: bold; margin-bottom: 10px;">PHASE 1 COMPLÉTÉE AVEC SUCCÈS</div>
            <p><strong>Organisation créée sans adhérents</strong></p>
            <p>Votre organisation a été enregistrée avec succès. Les adhérents pourront être ajoutés en Phase 2.</p>
        </div>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Informations du dossier</h3>
            <p><strong>Numéro de dossier :</strong> ' . $data['dossier']->numero_dossier . '</p>
            <p><strong>Numéro de récépissé :</strong> ' . $data['numero_recepisse'] . '</p>
            <p><strong>Date de soumission Phase 1 :</strong> ' . $data['dossier']->submitted_at->format('d/m/Y à H:i') . '</p>
            <p><strong>Type d\'organisation :</strong> ' . ucfirst(str_replace('_', ' ', $data['organisation']->type)) . '</p>
            <p><strong>Phase :</strong> 1/2 - Organisation créée SANS adhérents</p>
        </div>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Organisation créée</h3>
            <p><strong>Nom :</strong> ' . $data['organisation']->nom . '</p>
            <p><strong>Sigle :</strong> ' . ($data['organisation']->sigle ?? 'Non renseigné') . '</p>
            <p><strong>Province :</strong> ' . $data['organisation']->province . '</p>
            <p><strong>Statut :</strong> Organisation créée, en attente des adhérents</p>
        </div>
        
        <div class="next-steps" style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0;">
            <h3>PROCHAINES ÉTAPES - PHASE 2</h3>
            <p><strong>Étape suivante :</strong> Ajout des adhérents en Phase 2</p>
            <p><strong>Comment procéder :</strong></p>
            <ol>
                <li>Connectez-vous à la plateforme PNGDI</li>
                <li>Accédez au menu "Import des adhérents"</li>
                <li>Utilisez le numéro de dossier : <strong>' . $data['dossier']->numero_dossier . '</strong></li>
                <li>Téléchargez et complétez la liste des adhérents</li>
                <li>Soumettez les adhérents pour validation</li>
            </ol>
            <p><strong>Important :</strong> Votre dossier restera en attente tant que les adhérents ne seront pas ajoutés.</p>
        </div>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Traitement et validation</h3>
            <p>1. Votre dossier Phase 1 sera examiné dans l\'ordre d\'arrivée (système FIFO)</p>
            <p>2. Un agent sera assigné sous 48h ouvrées</p>
            <p>3. Vous serez notifié par email des étapes suivantes</p>
            <p>4. Délai de traitement complet : 72 heures ouvrées après ajout des adhérents</p>
        </div>
        
        <div class="footer" style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
            <p>Document généré automatiquement le ' . $data['date_generation']->format('d/m/Y à H:i') . '</p>
            <p>Plateforme Numérique Gabonaise de Déclaration des Intentions (PNGDI)</p>
            <p><strong>Phase 1 complétée - Phase 2 en attente</strong></p>
        </div>';

        return \App\Helpers\PdfTemplateHelper::wrapContent(
            'Accusé de Réception Phase 1 - ' . $data['dossier']->numero_dossier,
            $content
        );
    }



    /**
     * 🔧 NOUVELLE MÉTHODE : Nettoyer les données de session expirées
     */
    private function cleanupExpiredSessionData()
    {
        try {
            $allSessionData = session()->all();
            $cleanedCount = 0;

            foreach ($allSessionData as $key => $value) {
                // Chercher les clés d'expiration Phase 2
                if (strpos($key, 'phase2_expires_') === 0) {
                    $expirationTime = $value;

                    if (now()->isAfter($expirationTime)) {
                        // Session expirée, nettoyer
                        $dossierId = str_replace('phase2_expires_', '', $key);
                        $adherentsKey = 'phase2_adherents_' . $dossierId;

                        session()->forget([$key, $adherentsKey]);
                        $cleanedCount++;

                        \Log::info('🧹 Session Phase 2 expirée nettoyée', [
                            'dossier_id' => $dossierId,
                            'expired_at' => $expirationTime
                        ]);
                    }
                }
            }

            if ($cleanedCount > 0) {
                \Log::info('✅ Nettoyage sessions Phase 2 terminé', [
                    'cleaned_count' => $cleanedCount
                ]);
            }

            return $cleanedCount;

        } catch (\Exception $e) {
            \Log::error('❌ Erreur nettoyage sessions Phase 2', [
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }


    /**
     * Afficher les détails d'une organisation
     */
    public function show(Organisation $organisation)
    {
        $this->authorize('view', $organisation);

        $organisation->load([
            'dossier.validations.entity',
            'adherents',
            'fondateurs',
            'etablissements',
            'documents'
        ]);

        return view('operator.organisations.show', compact('organisation'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Organisation $organisation)
    {
        $this->authorize('update', $organisation);

        if (in_array($organisation->statut, ['soumis', 'en_cours', 'approuve'])) {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('warning', 'Cette organisation ne peut plus être modifiée car elle est en cours de traitement.');
        }

        $provinces = $this->getProvinces();
        $documentTypes = $this->getRequiredDocuments($organisation->type);

        return view('operator.organisations.edit', compact(
            'organisation',
            'provinces',
            'documentTypes'
        ));
    }

    /**
     * Mettre à jour une organisation
     */
    public function update(Request $request, Organisation $organisation)
    {
        $this->authorize('update', $organisation);

        if (in_array($organisation->statut, ['soumis', 'en_cours', 'approuve'])) {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('error', 'Cette organisation ne peut plus être modifiée.');
        }

        $validatedData = $this->validateOrganisationData($request, $organisation->type);

        try {
            $organisation->update($validatedData);

            return redirect()->route('operator.organisations.show', $organisation)
                ->with('success', 'Organisation mise à jour avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour')
                ->withInput();
        }
    }

    /**
     * Valider une organisation (méthode renommée pour éviter le conflit)
     */
    public function validateOrganisation(Organisation $organisation)
    {
        $this->authorize('validate', $organisation);

        try {
            $validation = $this->organisationValidationService->validateOrganisation($organisation);

            if ($validation['success']) {
                return redirect()->route('operator.organisations.show', $organisation)
                    ->with('success', 'Organisation validée avec succès.');
            } else {
                return redirect()->route('operator.organisations.show', $organisation)
                    ->with('error', 'Validation échouée: ' . $validation['message']);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur validation organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la validation');
        }
    }

    /**
     * Soumettre une organisation pour traitement
     */
    public function submit(Organisation $organisation)
    {
        $this->authorize('submit', $organisation);

        if ($organisation->statut !== 'brouillon') {
            return redirect()->route('operator.organisations.show', $organisation)
                ->with('error', 'Cette organisation a déjà été soumise.');
        }

        try {
            \DB::beginTransaction();

            $missingDocuments = $this->checkRequiredDocuments($organisation);
            if (!empty($missingDocuments)) {
                return redirect()->route('operator.organisations.edit', $organisation)
                    ->with('error', 'Documents manquants: ' . implode(', ', $missingDocuments));
            }

            $organisation->update(['statut' => 'soumis']);
            $organisation->dossier->update(['statut' => 'soumis']);

            $this->workflowService->startWorkflow($organisation->dossier);

            \DB::commit();

            return redirect()->route('operator.organisations.show', $organisation)
                ->with('success', 'Organisation soumise avec succès. Elle sera traitée selon l\'ordre d\'arrivée.');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Erreur soumission organisation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la soumission');
        }
    }

    /**
     * Vérifier si des NIP sont déjà membres actifs d'autres organisations
     */
    public function checkExistingMembers(Request $request)
    {
        $nips = $request->input('nips', []);

        if (empty($nips)) {
            return response()->json(['existing_nips' => []]);
        }

        $existingNips = \App\Models\Adherent::whereIn('nip', $nips)
            ->where('is_active', true)
            ->pluck('nip')
            ->unique()
            ->values()
            ->toArray();

        return response()->json([
            'existing_nips' => $existingNips,
            'count' => count($existingNips)
        ]);
    }



    /**
     * ✅ MÉTHODE UTILITAIRE : Générer numéro de soumission unique
     */
    private function generateNumeroSoumission()
    {
        $year = date('Y');
        $month = date('m');

        // Compter les soumissions du mois
        $count = \App\Models\Dossier::where('statut', 'soumis_administration')
            ->whereYear('submitted_at', $year)
            ->whereMonth('submitted_at', $month)
            ->count() + 1;

        return sprintf('ADMIN-%s%s-%05d', $year, $month, $count);
    }




    /**
     * Afficher la page de confirmation après soumission d'organisation
     */
    public function confirmation($dossierId)
    {
        try {
            $dossier = Dossier::with([
                'organisation',
                'documents'
            ])->findOrFail($dossierId);

            // Vérifier l'accès
            if ($dossier->organisation->user_id !== auth()->id()) {
                abort(403, 'Accès non autorisé à ce dossier.');
            }

            // Vérifier que le dossier vient d'être soumis (dans les dernières 24h)
            if ($dossier->submitted_at->diffInHours(now()) > 24) {
                return redirect()->route('operator.dashboard')
                    ->with('warning', 'Cette page de confirmation n\'est plus disponible.');
            }

            $sessionData = session('success_data');

            if (!$sessionData) {
                $sessionData = $this->reconstructConfirmationData($dossier);
            }

            $confirmationData = [
                'organisation' => $dossier->organisation,
                'dossier' => $dossier,
                'numero_recepisse' => $dossier->organisation->numero_recepisse,
                'adherents_stats' => $sessionData['adherents_stats'] ?? $this->calculateAdherentsStats($dossier),
                'accuse_reception_path' => $this->getAccuseReceptionPath($dossier),
                'delai_traitement' => '72 heures ouvrées',
                // MESSAGE CONFORME À LA LOI N° 016/2025 du 27 Juin 2025
                'message_confirmation' => 'Votre dossier numérique a été soumis avec succès. Aux fins de recevoir votre accusé de réception, conformément aux dispositions de l\'article 26 de la loi N° 016/2025 du 27 Juin 2025 relative aux partis politiques en République Gabonaise, vous êtes invité à déposer votre dossier physique, en 3 exemplaires, auprès des services de la Direction Générale des Élections et des Libertés Publiques du Ministère de l\'Intérieur, de la Sécurité et de la Décentralisation, en application des dispositions de l\'article 24 de la loi suscitée.',
                'message_legal' => [
                    'loi_reference' => 'Loi N° 016/2025 du 27 Juin 2025',
                    'article_reference' => 'Articles 24 et 26',
                    'depot_requis' => 'Dossier physique en 3 exemplaires',
                    'service_depot' => 'Direction Générale des Élections et des Libertés Publiques',
                    'ministere' => 'Ministère de l\'Intérieur, de la Sécurité et de la Décentralisation'
                ]
            ];

            session()->forget('success_data');

            \Log::info('Page de confirmation consultée v3', [
                'user_id' => auth()->id(),
                'dossier_id' => $dossier->id,
                'organisation_nom' => $dossier->organisation->nom,
                'access_time' => now(),
                'numero_dossier' => $dossier->numero_dossier
            ]);

            return view('operator.dossiers.confirmation', compact('confirmationData'));

        } catch (\Exception $e) {
            \Log::error('Erreur affichage confirmation v3: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'dossier_id' => $dossierId,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->route('operator.dashboard')
                ->with('error', 'Impossible d\'afficher la page de confirmation.');
        }
    }

    /**
     * Télécharger l'accusé de réception
     */
    public function downloadAccuse($path)
    {
        try {
            $filename = basename($path);
            $fullPath = storage_path('app/public/accuses_reception/' . $filename);

            // Protection contre le path traversal
            $realPath = realpath($fullPath);
            $allowedBasePath = realpath(storage_path('app/public/accuses_reception'));
            if (!$realPath || !$allowedBasePath || !str_starts_with($realPath, $allowedBasePath)) {
                abort(403, 'Accès non autorisé.');
            }

            if (!file_exists($realPath)) {
                abort(404, 'Fichier non trouvé.');
            }

            $document = \App\Models\Document::where('nom_fichier', $filename)
                ->whereHas('dossier.organisation', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->first();

            if (!$document) {
                abort(403, 'Accès non autorisé à ce document.');
            }

            \Log::info('Téléchargement accusé de réception v3', [
                'user_id' => auth()->id(),
                'document_id' => $document->id,
                'filename' => $filename,
                'download_time' => now()
            ]);

            return response()->download($realPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement accusé v3: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'path' => $path,
                'error' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Impossible de télécharger le fichier.');
        }
    }

    // =============================================================================
    // MÉTHODES PRIVÉES CONSERVÉES ET COMPLÉTÉES
    // =============================================================================

    /**
     * Vérifier les limites d'organisations par opérateur
     */
    private function checkOrganisationLimits($type)
    {
        $userId = auth()->id();

        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $existingCount = Organisation::where('user_id', $userId)
                    ->where('type', 'parti_politique')
                    ->whereIn('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'actif'])
                    ->count();

                if ($existingCount >= 1) {
                    return [
                        'success' => false,
                        'message' => 'Vous avez déjà un parti politique actif. Un opérateur ne peut créer qu\'un seul parti politique à la fois.'
                    ];
                }
                break;

            case 'confession':
            case 'confession_religieuse':
                $existingCount = Organisation::where('user_id', $userId)
                    ->where('type', 'confession_religieuse')
                    ->whereIn('statut', ['brouillon', 'soumis', 'en_cours', 'approuve', 'actif'])
                    ->count();

                if ($existingCount >= 1) {
                    return [
                        'success' => false,
                        'message' => 'Vous avez déjà une confession religieuse active. Un opérateur ne peut créer qu\'une seule confession religieuse à la fois.'
                    ];
                }
                break;

            case 'association':
            case 'ong':
                break;

            default:
                return [
                    'success' => false,
                    'message' => 'Type d\'organisation non reconnu.'
                ];
        }

        return ['success' => true];
    }

    /**
     * Validation complète des données - VERSION CONFORME À LA RÈGLE MÉTIER NIP
     * ✅ Enregistre TOUS les adhérents, même avec des NIP invalides
     * ✅ Marque les anomalies sans bloquer le processus
     */
    private function validateCompleteOrganisationData(Request $request, $type)
    {
        // ✅ NORMALISATION DES NOMS DE CHAMPS (formulaire → validation)
        // Le formulaire Blade envoie des noms différents de ceux attendus par la validation
        $mergeData = [];

        // 1. org_adresse → org_adresse_complete
        if ($request->has('org_adresse') && !$request->has('org_adresse_complete')) {
            $mergeData['org_adresse_complete'] = $request->input('org_adresse');
        }

        // 2. org_province_id → org_province (résoudre le nom depuis l'ID)
        if ($request->has('org_province_id') && !$request->has('org_province')) {
            $provinceId = $request->input('org_province_id');
            $province = \App\Models\Province::find($provinceId);
            $mergeData['org_province'] = $province ? $province->nom : $request->input('province', '');
        }
        // Fallback: utiliser le champ caché "province" (nom) si disponible
        if (empty($mergeData['org_province']) && $request->has('province') && !$request->has('org_province')) {
            $mergeData['org_province'] = $request->input('province');
        }

        // 2b. org_departement_id → org_departement (résoudre le nom depuis l'ID)
        if ($request->has('org_departement_id') && !$request->has('org_departement')) {
            $departementId = $request->input('org_departement_id');
            $departement = \App\Models\Departement::find($departementId);
            $mergeData['org_departement'] = $departement ? $departement->nom : '';
        }
        // Fallback: utiliser le champ caché "departement" (nom) si disponible
        if (empty($mergeData['org_departement']) && $request->has('departement') && !$request->has('org_departement')) {
            $mergeData['org_departement'] = $request->input('departement');
        }

        // 3. zone_type → org_zone_type
        if ($request->has('zone_type') && !$request->has('org_zone_type')) {
            $mergeData['org_zone_type'] = $request->input('zone_type');
        }
        // Fallback: zone_type_radio
        if (empty($mergeData['org_zone_type']) && $request->has('zone_type_radio') && !$request->has('org_zone_type')) {
            $mergeData['org_zone_type'] = $request->input('zone_type_radio');
        }

        // 4. Normalisation des checkboxes : JS envoie "true"/"false" (string) via FormData
        // Laravel 'accepted' attend : "yes", "on", 1, "1", true (boolean)
        // "true" (string) n'est PAS accepté → convertir en "1"
        $checkboxFields = [
            'declaration_veracite',
            'declaration_conformite',
            'declaration_autorisation',
            'declaration_exclusivite_parti',
            'guide_read_confirm',
        ];
        foreach ($checkboxFields as $field) {
            $val = $request->input($field);
            if ($val === 'true' || $val === true || $val === 'on' || $val === '1' || $val === 1) {
                $mergeData[$field] = '1';
            } elseif ($val === 'false' || $val === false || $val === '0' || $val === 0) {
                // Ne pas merger : laisser absent pour que 'sometimes' passe
            }
        }

        // 5. Fondateurs : s'assurer qu'un tableau vide existe si aucun fondateur envoyé
        $fondateurs = $request->input('fondateurs');
        if (is_null($fondateurs) && !$request->has('fondateurs')) {
            // Le champ est totalement absent - ne rien faire, la validation 'required' signalera l'erreur
            \Log::warning('⚠️ Aucun fondateur reçu dans la requête');
        } elseif (is_string($fondateurs)) {
            // Si envoyé comme JSON string, décoder
            $decoded = json_decode($fondateurs, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $mergeData['fondateurs'] = $decoded;
            }
        }

        if (!empty($mergeData)) {
            $request->merge($mergeData);
            \Log::info('✅ Normalisation champs formulaire → validation', [
                'merged_fields' => array_keys($mergeData),
                'merged_values_summary' => collect($mergeData)->map(function($v) {
                    return is_array($v) ? '[array:' . count($v) . ']' : $v;
                })->toArray()
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // 🔍 TEST PAR ÉLIMINATION — Vérification bloc par bloc
        // ═══════════════════════════════════════════════════════════════
        \Log::info('🔍 TEST ÉLIMINATION — Données reçues par bloc', [
            'ONGLET_1_TYPE' => [
                'type_organisation' => $request->input('type_organisation'),
            ],
            'ONGLET_2_GUIDE' => [
                'guide_read_confirm' => $request->input('guide_read_confirm'),
            ],
            'ONGLET_3_DEMANDEUR' => [
                'demandeur_nip' => $request->input('demandeur_nip'),
                'demandeur_nom' => $request->input('demandeur_nom'),
                'demandeur_prenom' => $request->input('demandeur_prenom'),
                'demandeur_email' => $request->input('demandeur_email'),
                'demandeur_telephone' => $request->input('demandeur_telephone'),
            ],
            'ONGLET_4_ORGANISATION' => [
                'org_nom' => $request->input('org_nom'),
                'org_sigle' => $request->input('org_sigle'),
                'org_objet' => substr($request->input('org_objet', ''), 0, 50) . '...',
                'org_date_creation' => $request->input('org_date_creation'),
                'org_telephone' => $request->input('org_telephone'),
            ],
            'ONGLET_5_LOCALISATION' => [
                'org_adresse_complete' => $request->input('org_adresse_complete'),
                'org_province' => $request->input('org_province'),
                'org_departement' => $request->input('org_departement'),
                'org_zone_type' => $request->input('org_zone_type'),
                'BRUT_org_adresse' => $request->input('org_adresse'),
                'BRUT_org_province_id' => $request->input('org_province_id'),
                'BRUT_zone_type' => $request->input('zone_type'),
                'BRUT_province_hidden' => $request->input('province'),
            ],
            'ONGLET_6_FONDATEURS' => [
                'fondateurs_present' => $request->has('fondateurs'),
                'fondateurs_type' => gettype($request->input('fondateurs')),
                'fondateurs_count' => is_array($request->input('fondateurs')) ? count($request->input('fondateurs')) : 'N/A',
                'fondateurs_sample' => is_array($request->input('fondateurs')) ? array_keys($request->input('fondateurs')[0] ?? []) : 'ABSENT',
            ],
            'ONGLET_7_DOCUMENTS' => [
                'has_files' => $request->hasFile('documents'),
            ],
            'ONGLET_8_DECLARATIONS' => [
                'declaration_veracite' => $request->input('declaration_veracite'),
                'declaration_conformite' => $request->input('declaration_conformite'),
                'declaration_autorisation' => $request->input('declaration_autorisation'),
                'declaration_exclusivite_parti' => $request->input('declaration_exclusivite_parti'),
                'submission_mode' => $request->input('submission_mode'),
                '_phase' => $request->input('_phase'),
            ],
        ]);

        $rules = [
            // ÉTAPE 1 : Type
            'type_organisation' => 'required|in:association,ong,parti_politique,confession_religieuse',

            // ÉTAPE 2 : Guide
            'guide_read_confirm' => 'sometimes|accepted',

            // ÉTAPE 3 : Demandeur - COLONNES CONFORMES À USERS TABLE
            'demandeur_nip' => [
                'required',
                'string',
                'regex:/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validateNipFormat($value)) {
                        $fail('Le format du NIP est invalide. Format attendu: XX-QQQQ-YYYYMMDD');
                    }
                }
            ],
            'demandeur_nom' => 'required|string|max:255',
            'demandeur_prenom' => 'required|string|max:255',
            'demandeur_email' => 'required|email|max:255',
            'demandeur_telephone' => 'required|string|max:20',
            'demandeur_role' => 'nullable|string|max:255',
            'demandeur_civilite' => 'nullable|in:M,Mme,Mlle',
            'demandeur_date_naissance' => 'nullable|date|before:-18 years',
            'demandeur_nationalite' => 'nullable|string|max:255',
            'demandeur_adresse' => 'nullable|string|max:500',
            'demandeur_profession' => 'nullable|string|max:255',

            // ÉTAPE 4 : Organisation - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_nom' => 'required|string|max:255|unique:organisations,nom',
            'org_sigle' => 'nullable|string|max:255|unique:organisations,sigle',
            'org_objet' => 'required|string|min:50',
            'org_date_creation' => 'required|date',
            'org_telephone' => 'required|string|max:255',
            'org_email' => 'nullable|email|max:255',
            'org_site_web' => 'nullable|url|max:255',
            'org_domaine' => 'nullable|string|max:255',

            // ÉTAPE 5 : Coordonnées - COLONNES CONFORMES À ORGANISATIONS TABLE
            'org_adresse_complete' => 'required|string|max:255',
            'org_province' => 'required|string|max:255',
            'org_departement' => 'nullable|string|max:255',
            'org_prefecture' => 'nullable|string|max:255',
            'org_zone_type' => 'required|in:urbaine,rurale',
            'org_latitude' => 'nullable|numeric|between:-3.978,2.318',
            'org_longitude' => 'nullable|numeric|between:8.695,14.502',

            // ÉTAPE 6 : Fondateurs - VALIDATION AVEC RÈGLE MÉTIER APPLIQUÉE
            'fondateurs' => [
                'required',
                function ($attribute, $value, $fail) use ($type) {
                    // Décoder JSON si c'est une string
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('Les données des fondateurs sont invalides (JSON malformé): ' . json_last_error_msg());
                            return;
                        }
                        $value = $decoded;
                        request()->merge(['fondateurs' => $value]);
                    }

                    if (!is_array($value)) {
                        $fail('Les fondateurs doivent être un tableau.');
                        return;
                    }

                    $minRequired = $this->getMinFondateurs($type);
                    if (count($value) < $minRequired) {
                        $fail("Minimum {$minRequired} fondateurs requis pour ce type d'organisation.");
                    }

                    // ✅ VALIDATION SOUPLE POUR FONDATEURS - CONFORME RÈGLE MÉTIER
                    foreach ($value as $index => $fondateur) {
                        if (!is_array($fondateur)) {
                            $fail("Le fondateur ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }

                        // ✅ NIP : VALIDATION NON-BLOQUANTE
                        // Les anomalies NIP seront détectées lors de la création, pas ici
                        // ✅ VALIDATION NOUVEAU FORMAT NIP
                        if (empty($fondateur['nip'])) {
                            $fail("Le NIP du fondateur ligne " . ($index + 1) . " ne peut pas être vide.");
                        } else
                            if (!empty($fondateur['nip']) && !$this->validateNipFormat($fondateur['nip'])) {
                                \Log::info('NIP invalide détecté (sera enregistré comme anomalie)', [
                                    'ligne' => $index + 1,
                                    'nip' => $fondateur['nip'],
                                    'sera_traite_comme' => 'anomalie_majeure'
                                ]);
                            }

                        // Autres validations obligatoires
                        if (empty($fondateur['nom']) || empty($fondateur['prenom'])) {
                            $fail("Le nom et prénom du fondateur ligne " . ($index + 1) . " sont obligatoires.");
                        }
                        if (empty($fondateur['fonction'])) {
                            $fail("La fonction du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                        if (empty($fondateur['telephone'])) {
                            $fail("Le téléphone du fondateur ligne " . ($index + 1) . " est obligatoire.");
                        }
                    }
                }
            ],

            // ÉTAPE 7 : Adhérents - VALIDATION CONFORME À LA RÈGLE MÉTIER NIP
            'adherents' => [
                'nullable',
                function ($attribute, $value, $fail) use ($type) {
                    /**
                     * ✅ ÉTAPE 2 : REMPLACER LE CONTENU DE LA FONCTION adherents
                     * 
                     * CHERCHER : 'adherents' => [ 'nullable', function ($attribute, $value, $fail) use ($type) {
                     * REMPLACER tout le contenu entre { et } par :
                     */

                    // ✅ DÉTECTER SI ON EST EN PHASE 1
                    $isPhase1 = request()->has('__phase_1_validation');

                    \Log::info('🔍 VALIDATION ADHÉRENTS', [
                        'is_phase_1' => $isPhase1,
                        'value_type' => gettype($value),
                        'value_count' => is_array($value) ? count($value) : 'not_array'
                    ]);

                    // Si aucun adhérent fourni, c'est OK (Phase 1)
                    if (empty($value) || !is_array($value)) {
                        if ($isPhase1) {
                            \Log::info('✅ PHASE 1 : Aucun adhérent requis');
                            return; // ✅ Validation passée pour Phase 1
                        } else {
                            $fail('Les adhérents sont obligatoires en Phase 2.');
                            return;
                        }
                    }

                    $minRequired = $this->getMinAdherents($type);
                    $adherentsCount = count($value);

                    // Récupérer le nombre de fondateurs pour comparaison
                    $fondateurs = request()->input('fondateurs', []);
                    if (is_string($fondateurs)) {
                        $fondateurs = json_decode($fondateurs, true) ?? [];
                    }
                    $fondateursCount = is_array($fondateurs) ? count($fondateurs) : 0;

                    \Log::info('🔍 VÉRIFICATION ADHÉRENTS', [
                        'is_phase_1' => $isPhase1,
                        'adherents_count' => $adherentsCount,
                        'fondateurs_count' => $fondateursCount,
                        'min_required' => $minRequired
                    ]);

                    // ✅ VÉRIFICATION PHASE 1 : Si on n'a que les fondateurs convertis, c'est OK
                    if ($isPhase1 && $adherentsCount <= $fondateursCount + 5) { // Marge de tolérance
                        \Log::info('✅ PHASE 1 : Validation allégée activée');

                        // Validation de base seulement pour Phase 1
                        foreach ($value as $index => $adherent) {
                            if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                                $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                            }
                            if (empty($adherent['nip'])) {
                                $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " ne peut pas être vide.");
                            }

                            // ✅ PROFESSION : Valeur par défaut si manquante
                            if (empty($adherent['profession']) || trim($adherent['profession']) === '') {
                                $value[$index]['profession'] = 'A définir';
                                \Log::info('✅ Profession par défaut assignée', [
                                    'ligne' => $index + 1,
                                    'adherent' => ($adherent['nom'] ?? '') . ' ' . ($adherent['prenom'] ?? '')
                                ]);
                            }
                        }

                        // ✅ SORTIR EARLY POUR PHASE 1 - PAS DE VALIDATION 50 MIN
                        request()->merge(['adherents' => $value]);
                        return;
                    }

                    // Phase 2 : Validation complète normale
                    if ($adherentsCount < $minRequired) {
                        $fail("Minimum {$minRequired} adhérents requis pour ce type d'organisation.");
                    }

                    // Validation détaillée pour Phase 2
                    foreach ($value as $index => $adherent) {
                        if (!is_array($adherent)) {
                            $fail("L'adhérent ligne " . ($index + 1) . " doit être un objet valide.");
                            continue;
                        }

                        if (empty($adherent['nom']) || empty($adherent['prenom'])) {
                            $fail("Le nom et prénom de l'adhérent ligne " . ($index + 1) . " sont obligatoires.");
                        }

                        if (empty($adherent['nip'])) {
                            $fail("Le NIP de l'adhérent ligne " . ($index + 1) . " ne peut pas être vide.");
                        }

                        // Autres validations Phase 2...
                    }
                }


            ],

            // ÉTAPE 9 : Déclarations finales
            'declaration_veracite' => 'sometimes|accepted',
            'declaration_conformite' => 'sometimes|accepted',
            'declaration_autorisation' => 'sometimes|accepted'
        ];

        // Règles spécifiques pour parti politique
        if ($type === 'parti_politique') {
            $rules['declaration_exclusivite_parti'] = 'sometimes|accepted';
            $rules['adherents'][] = function ($attribute, $value, $fail) {
                if (is_array($value) && count($value) < 2) {
                    $fail("Un parti politique doit avoir au minimum 50 adhérents.");
                }
            };
        }

        $messages = [
            'demandeur_nip.required' => 'Le NIP du demandeur est obligatoire.',
            'demandeur_nip.regex' => 'Le NIP doit respecter le format XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225).',
            'org_nom.unique' => 'Ce nom d\'organisation est déjà utilisé.',
            'org_sigle.unique' => 'Ce sigle est déjà utilisé.',
            'org_objet.min' => 'L\'objet de l\'organisation doit contenir au moins 50 caractères.',
            'org_objet.required' => 'L\'objet de l\'organisation est obligatoire.',
            'declaration_exclusivite_parti.required' => 'La déclaration d\'exclusivité pour parti politique est obligatoire.',
            'declaration_exclusivite_parti.accepted' => 'Vous devez accepter la déclaration d\'exclusivité.',
            '*.accepted' => 'Cette déclaration est obligatoire.',
            '*.required' => 'Ce champ est obligatoire.'
        ];

        try {
            $validated = $request->validate($rules, $messages);

            // Post-traitement avec nettoyage des données
            if (isset($validated['fondateurs'])) {
                if (is_string($validated['fondateurs'])) {
                    $decoded = json_decode($validated['fondateurs'], true);
                    $validated['fondateurs'] = $decoded ?? [];
                }
                if (!is_array($validated['fondateurs'])) {
                    $validated['fondateurs'] = [];
                }

                // ✅ NETTOYER LES NIP DES FONDATEURS
                foreach ($validated['fondateurs'] as &$fondateur) {
                    if (isset($fondateur['nip'])) {
                        $fondateur['nip'] = $this->cleanNipForStorage($fondateur['nip']);
                    }
                }
            }

            if (isset($validated['adherents'])) {
                if (is_string($validated['adherents'])) {
                    $decoded = json_decode($validated['adherents'], true);
                    $validated['adherents'] = $decoded ?? [];
                }
                if (!is_array($validated['adherents'])) {
                    $validated['adherents'] = [];
                }

                // ✅ NETTOYER LES NIP DES ADHÉRENTS
                foreach ($validated['adherents'] as &$adherent) {
                    if (isset($adherent['nip'])) {
                        $adherent['nip'] = $this->cleanNipForStorage($adherent['nip']);
                    }

                    // Assurer la fonction par défaut
                    if (empty($adherent['fonction'])) {
                        $adherent['fonction'] = 'Membre';
                    }
                }
            }

            // Ajouter des valeurs par défaut
            $validated['org_departement'] = $request->input('org_departement');
            $validated['declaration_veracite'] = $request->has('declaration_veracite');
            $validated['declaration_conformite'] = $request->has('declaration_conformite');
            $validated['declaration_autorisation'] = $request->has('declaration_autorisation');
            $validated['guide_read_confirm'] = $request->has('guide_read_confirm');

            \Log::info('Validation v5 réussie - Règle métier NIP appliquée', [
                'fondateurs_count' => count($validated['fondateurs'] ?? []),
                'adherents_count' => count($validated['adherents'] ?? []),
                'type' => $type,
                'validation_version' => 'conforme_regle_metier_PNGDI_v5',
                'nip_validation' => 'non_bloquante_avec_detection_anomalies'
            ]);

            return $validated;

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur validation v5 avec règle métier', [
                'errors' => $e->errors(),
                'type' => $type,
                'validation_version' => 'conforme_regle_metier_PNGDI_v5'
            ]);

            throw $e;
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Nettoyer un NIP pour stockage
     * Conforme à la règle métier PNGDI
     */
    private function cleanNipForStorage($nip)
    {
        if (empty($nip)) {
            return '';
        }

        // Supprimer espaces et caractères indésirables, conserver les tirets
        $cleaned = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($nip));

        // Log du nettoyage pour traçabilité
        if ($cleaned !== $nip) {
            \Log::info('NIP nettoyé pour stockage', [
                'original' => $nip,
                'cleaned' => $cleaned
            ]);
        }

        return $cleaned;
    }

    /**
     * ✅ MÉTHODE MISE À JOUR : Créer les adhérents avec détection d'anomalies NIP
     * Conforme à la règle métier PNGDI
     */
    private function createAdherents(Organisation $organisation, array $adherentsData)
    {
        $stats = [
            'total' => count($adherentsData),
            'valides' => 0,
            'anomalies_critiques' => 0,
            'anomalies_majeures' => 0,
            'anomalies_mineures' => 0
        ];

        $anomalies = [];
        $adherentsCreated = [];

        foreach ($adherentsData as $index => $adherentData) {
            // ✅ DÉTECTER LES ANOMALIES NIP SELON LA RÈGLE MÉTIER
            $anomaliesDetectees = $this->detectAndManageNipAnomalies($adherentData, $organisation->type, $organisation->id);

            // Historique conforme à la règle métier
            $historiqueData = [
                'creation' => now()->toISOString(),
                'anomalies_detectees' => $anomaliesDetectees,
                'source' => 'creation_organisation',
                'regle_metier' => 'enregistrement_avec_anomalies_PNGDI',
                'profession_originale' => $adherentData['profession'] ?? null,
                'fonction_originale' => $adherentData['fonction'] ?? 'Membre'
            ];

            // ✅ ENREGISTRER L'ADHÉRENT MÊME AVEC ANOMALIES NIP
            $adherentDataCleaned = [
                'organisation_id' => $organisation->id,
                'nip' => $adherentData['nip'], // NIP tel que fourni
                'nom' => strtoupper($adherentData['nom']),
                'prenom' => $adherentData['prenom'],
                'profession' => $adherentData['profession'] ?? null,
                'fonction' => $adherentData['fonction'] ?? 'Membre',
                'telephone' => $adherentData['telephone'] ?? null,
                'email' => $adherentData['email'] ?? null,
                'date_adhesion' => now(),

                // ✅ MARQUER LES ANOMALIES SELON LA RÈGLE MÉTIER
                'has_anomalies' => !empty($anomaliesDetectees),
                'anomalies_data' => json_encode($anomaliesDetectees, JSON_UNESCAPED_UNICODE),
                'anomalies_severity' => $this->resolveSeverity($anomaliesDetectees),

                // ✅ RESTE ACTIF MÊME AVEC ANOMALIES (sauf critiques)
                'is_active' => empty($anomaliesDetectees['critiques']),

                'historique' => json_encode($historiqueData, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $adherent = \App\Models\Adherent::create($adherentDataCleaned);
            $adherentsCreated[] = $adherent;

            // Comptabiliser selon les anomalies détectées
            if (empty($anomaliesDetectees)) {
                $stats['valides']++;
            } else {
                if (!empty($anomaliesDetectees['critiques'])) {
                    $stats['anomalies_critiques']++;
                }
                if (!empty($anomaliesDetectees['majeures'])) {
                    $stats['anomalies_majeures']++;
                }
                if (!empty($anomaliesDetectees['mineures'])) {
                    $stats['anomalies_mineures']++;
                }

                $anomalies[] = [
                    'adherent_id' => $adherent->id,
                    'ligne' => $index + 1,
                    'nip' => $adherentData['nip'],
                    'nom_complet' => $adherentData['nom'] . ' ' . $adherentData['prenom'],
                    'profession' => $adherentData['profession'] ?? null,
                    'fonction' => $adherentData['fonction'] ?? 'Membre',
                    'anomalies' => $anomaliesDetectees,
                    'severity' => $this->resolveSeverity($anomaliesDetectees)
                ];
            }
        }

        \Log::info('Adhérents créés avec règle métier NIP', [
            'total_crees' => count($adherentsCreated),
            'stats' => $stats,
            'anomalies_count' => count($anomalies),
            'regle_metier' => 'PNGDI_enregistrement_avec_anomalies'
        ]);

        return [
            'adherents' => $adherentsCreated,
            'stats' => $stats,
            'anomalies' => $anomalies
        ];
    }

    /**
     * ✅ MÉTHODE MISE À JOUR : Détecter les anomalies selon la règle métier PNGDI
     * Inclut maintenant les professions exclues comme anomalie critique
     */
    private function detectAndManageNipAnomalies(array $adherentData, string $typeOrganisation, int $organisationId)
    {
        $anomalies = [
            'critiques' => [],
            'majeures' => [],
            'mineures' => []
        ];

        $nip = $adherentData['nip'] ?? '';
        $profession = $adherentData['profession'] ?? '';

        // ✅ ANOMALIE : FORMAT NIP INCORRECT - NOUVEAU FORMAT
        if (!$this->validateNipFormat($nip)) {
            $anomalies['majeures'][] = [
                'code' => 'NIP_INVALID_FORMAT',
                'message' => 'Le NIP doit respecter le format XX-QQQQ-YYYYMMDD (ex: A1-2345-19901225).',
                'nip_fourni' => $nip,
                'format_attendu' => 'XX-QQQQ-YYYYMMDD'
            ];
        } else {
            // Si format correct, extraire la date de naissance pour validation additionnelle
            $birthDate = $this->extractBirthDateFromNip($nip);
            if ($birthDate) {
                $age = $birthDate->diffInYears(now());

                // Validation âge raisonnable (18-100 ans)
                if ($age < 18) {
                    $anomalies['critiques'][] = [
                        'code' => 'AGE_TOO_YOUNG',
                        'message' => 'Personne mineure détectée (âge: ' . $age . ' ans).',
                        'nip' => $nip,
                        'age_calcule' => $age
                    ];
                } elseif ($age > 100) {
                    $anomalies['majeures'][] = [
                        'code' => 'AGE_SUSPICIOUS',
                        'message' => 'Âge suspect détecté (âge: ' . $age . ' ans).',
                        'nip' => $nip,
                        'age_calcule' => $age
                    ];
                }
            }
        }

        // ✅ ANOMALIE : NIP DÉJÀ DANS UN AUTRE PARTI POLITIQUE
        if ($typeOrganisation === 'parti_politique') {
            $existingInOtherParty = \App\Models\Adherent::whereHas('organisation', function ($query) use ($organisationId) {
                $query->where('type', 'parti_politique')
                    ->where('id', '!=', $organisationId);
            })->where('nip', $nip)->exists();

            if ($existingInOtherParty) {
                $anomalies['critiques'][] = [
                    'code' => 'NIP_DUPLICATE_OTHER_PARTY',
                    'message' => 'Ce NIP appartient déjà à un autre parti politique.',
                    'nip' => $nip
                ];
            }
        }

        // ✅ ANOMALIE CRITIQUE : PROFESSION EXCLUE POUR PARTI POLITIQUE
        if ($typeOrganisation === 'parti_politique' && !empty($profession)) {
            $professionsExclues = $this->getProfessionsExcluesParti();
            if (in_array(strtolower($profession), array_map('strtolower', $professionsExclues))) {
                $anomalies['critiques'][] = [
                    'code' => 'PROFESSION_EXCLUE_PARTI',
                    'message' => 'Profession exclue pour les partis politiques: ' . $profession,
                    'profession_fournie' => $profession,
                    'type_organisation' => $typeOrganisation,
                    'regle_legale' => 'Article 15 - Loi N° 016/2025'
                ];
            }
        }

        // ✅ ANOMALIE : DOUBLON DANS LA MÊME ORGANISATION
        $existingInSameOrg = \App\Models\Adherent::where('organisation_id', $organisationId)
            ->where('nip', $nip)
            ->exists();

        if ($existingInSameOrg) {
            $anomalies['majeures'][] = [
                'code' => 'NIP_DUPLICATE_SAME_ORG',
                'message' => 'Ce NIP apparaît plusieurs fois dans cette organisation.',
                'nip' => $nip
            ];
        }

        // ✅ ANOMALIE MINEURE : INFORMATIONS DE CONTACT MANQUANTES
        if (empty($adherentData['telephone']) && empty($adherentData['email'])) {
            $anomalies['mineures'][] = [
                'code' => 'CONTACT_INCOMPLET',
                'message' => 'Aucun moyen de contact fourni (téléphone ou email).',
                'telephone' => $adherentData['telephone'] ?? null,
                'email' => $adherentData['email'] ?? null
            ];
        }

        return $anomalies;
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Résoudre la sévérité des anomalies
     */
    private function resolveSeverity(array $anomalies)
    {
        if (!empty($anomalies['critiques'])) {
            return 'critique';
        }
        if (!empty($anomalies['majeures'])) {
            return 'majeure';
        }
        if (!empty($anomalies['mineures'])) {
            return 'mineure';
        }
        return null;
    }

    /**
     * Méthode d'aide pour nettoyer les données JSON
     */
    private function sanitizeJsonData($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            return $data; // Retourner la string si le décodage échoue
        }

        if (is_array($data)) {
            // Nettoyer récursivement les tableaux
            $cleaned = [];
            foreach ($data as $key => $value) {
                $cleaned[$key] = $this->sanitizeJsonData($value);
            }
            return $cleaned;
        }

        return $data;
    }

    /**
     * Générer un numéro de dossier unique
     */
    private function generateDossierNumber($type)
    {
        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $prefix = 'PP';
                break;
            case 'confession':
            case 'confession_religieuse':
                $prefix = 'CR';
                break;
            case 'association':
                $prefix = 'AS';
                break;
            case 'ong':
                $prefix = 'ONG';
                break;
            default:
                $prefix = 'ONG';
        }

        $year = date('Y');
        $sequence = Dossier::where('numero_dossier', 'LIKE', $prefix . $year . '%')->count() + 1;

        return $prefix . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtenir le nombre minimum de fondateurs requis — depuis la BD (organisation_types)
     */
    private function getMinFondateurs($type)
    {
        $orgType = \App\Models\OrganisationType::where('code', $type)->first();
        if ($orgType) {
            return $orgType->nb_min_fondateurs_majeurs;
        }
        // Fallback si type non trouvé en BD
        return 2;
    }

    /**
     * Obtenir le nombre minimum d'adhérents requis — depuis la BD (organisation_types)
     */
    private function getMinAdherents($type)
    {
        $orgType = \App\Models\OrganisationType::where('code', $type)->first();
        if ($orgType) {
            return $orgType->nb_min_adherents_creation;
        }
        // Fallback si type non trouvé en BD
        return 10;
    }

    /**
     * ========================================================================
     * MÉTHODES À AJOUTER DANS OrganisationController.php
     * ========================================================================
     */

    /**
     * Sauvegarder les adhérents en session (Étape 7)
     */
    public function saveSessionAdherents(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $expirationKey = $request->input('expiration_key');
            $data = $request->input('data');
            $dossierId = $request->input('dossier_id');

            \Log::info('💾 SAUVEGARDE SESSION ADHÉRENTS ÉTAPE 7', [
                'session_key' => $sessionKey,
                'dossier_id' => $dossierId,
                'adherents_count' => isset($data['data']) ? count($data['data']) : 0,
                'user_id' => auth()->id()
            ]);

            // Validation
            if (!$sessionKey || !$data || !$dossierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }

            // Vérifier que l'utilisateur a le droit sur ce dossier
            $dossier = Dossier::where('id', $dossierId)
                ->whereHas('organisation', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->first();

            if (!$dossier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dossier non trouvé ou accès non autorisé'
                ], 403);
            }

            // Sauvegarder en session avec structure exacte pour confirmation.blade.php
            session([
                $sessionKey => $data['data'], // Array direct des adhérents
                $expirationKey => $data['expires_at']
            ]);

            // Sauvegarder aussi les métadonnées séparément
            $metadataKey = str_replace('phase2_adherents_', 'phase2_metadata_', $sessionKey);
            session([
                $metadataKey => $data['metadata'] ?? []
            ]);

            \Log::info('✅ SESSION ADHÉRENTS SAUVEGARDÉE', [
                'session_key' => $sessionKey,
                'adherents_count' => count($data['data']),
                'expires_at' => $data['expires_at']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session sauvegardée avec succès',
                'data' => [
                    'adherents_count' => count($data['data']),
                    'expires_at' => $data['expires_at'],
                    'session_key' => $sessionKey
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR SAUVEGARDE SESSION ADHÉRENTS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier session adhérents existante
     */
    public function checkSessionAdherents(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');

            if (!$sessionKey || !$dossierId) {
                return response()->json([
                    'success' => false,
                    'exists' => false,
                    'message' => 'Paramètres manquants'
                ], 400);
            }

            // Vérifier session
            $sessionData = session($sessionKey);
            $expirationKey = str_replace('phase2_adherents_', 'phase2_expires_', $sessionKey);
            $expirationTime = session($expirationKey);

            if (!$sessionData) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => 'Aucune session trouvée'
                ]);
            }

            // Vérifier expiration
            if ($expirationTime && now()->isAfter($expirationTime)) {
                // Session expirée, nettoyer
                session()->forget([$sessionKey, $expirationKey]);

                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'message' => 'Session expirée'
                ]);
            }

            // Récupérer métadonnées
            $metadataKey = str_replace('phase2_adherents_', 'phase2_metadata_', $sessionKey);
            $metadata = session($metadataKey, []);

            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => [
                    'data' => $sessionData,
                    'total' => is_array($sessionData) ? count($sessionData) : 0,
                    'expires_at' => $expirationTime,
                    'metadata' => $metadata
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR VÉRIFICATION SESSION', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'exists' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    /**
     * Nettoyer session adhérents
     */
    public function clearSessionAdherents(Request $request)
    {
        try {
            $sessionKey = $request->input('session_key');
            $dossierId = $request->input('dossier_id');

            if (!$sessionKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session key manquante'
                ], 400);
            }

            // Nettoyer toutes les clés liées
            $expirationKey = str_replace('phase2_adherents_', 'phase2_expires_', $sessionKey);
            $metadataKey = str_replace('phase2_adherents_', 'phase2_metadata_', $sessionKey);

            session()->forget([$sessionKey, $expirationKey, $metadataKey]);

            \Log::info('🧹 SESSION ADHÉRENTS NETTOYÉE', [
                'session_key' => $sessionKey,
                'dossier_id' => $dossierId,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session nettoyée avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR NETTOYAGE SESSION', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }


    /**
     * Créer les fondateurs de l'organisation
     */
    private function createFondateurs(Organisation $organisation, array $fondateursData)
    {
        foreach ($fondateursData as $index => $fondateurData) {
            \App\Models\Fondateur::create([
                'organisation_id' => $organisation->id,
                'nip' => $fondateurData['nip'],
                'nom' => strtoupper($fondateurData['nom']),
                'prenom' => $fondateurData['prenom'],
                'fonction' => $fondateurData['fonction'],
                'telephone' => $fondateurData['telephone'],
                'email' => $fondateurData['email'] ?? null,
                'ordre' => $index + 1,

                // Colonnes supplémentaires de la table fondateurs si disponibles
                'date_naissance' => $fondateurData['date_naissance'] ?? null,
                'lieu_naissance' => $fondateurData['lieu_naissance'] ?? null,
                'sexe' => $fondateurData['sexe'] ?? null,
                'nationalite' => $fondateurData['nationalite'] ?? 'Gabonaise',
                'adresse_complete' => $fondateurData['adresse'] ?? null,
                'province' => $fondateurData['province'] ?? null,
                'departement' => $fondateurData['departement'] ?? null,
                'prefecture' => $fondateurData['prefecture'] ?? null,
                'zone_type' => $fondateurData['zone_type'] ?? 'urbaine',

                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Créer les membres du bureau de l'organisation
     */
    private function createMembresBureau(Organisation $organisation, array $membresBureauData)
    {
        foreach ($membresBureauData as $index => $membreData) {
            \App\Models\MembreBureau::create([
                'organisation_id' => $organisation->id,
                'nip' => $membreData['nip'],
                'nom' => strtoupper($membreData['nom']),
                'prenom' => $membreData['prenom'],
                'fonction' => $membreData['fonction'],
                'contact' => $membreData['contact'] ?? null,
                'domicile' => $membreData['domicile'] ?? null,
                'afficher_recepisse' => $membreData['afficher_recepisse'] ?? false,
                'ordre' => $index + 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        \Log::info('✅ Membres bureau créés', [
            'organisation_id' => $organisation->id,
            'count' => count($membresBureauData),
            'pour_recepisse' => collect($membresBureauData)->where('afficher_recepisse', true)->count()
        ]);
    }

    /**
     * Détecter les anomalies d'un adhérent
     */
    private function detectAnomaliesAdherent(array $adherentData, string $typeOrganisation)
    {
        $anomalies = [
            'critiques' => [],
            'majeures' => [],
            'mineures' => []
        ];

        $nip = $adherentData['nip'];

        // Anomalies critiques
        if (!preg_match('/^[0-9]{13}$/', $nip)) {
            $anomalies['critiques'][] = [
                'code' => 'nip_invalide',
                'message' => 'Format NIP incorrect (doit contenir 13 chiffres)',
                'recommandation' => 'Corriger le format du NIP'
            ];
        }

        // Vérifier si déjà membre actif ailleurs
        $existantAilleurs = \App\Models\Adherent::where('nip', $nip)
            ->where('is_active', true)
            ->with('organisation')
            ->first();

        if ($existantAilleurs) {
            if ($typeOrganisation === 'parti_politique') {
                $anomalies['critiques'][] = [
                    'code' => 'membre_existant_parti',
                    'message' => 'Déjà membre actif du parti: ' . $existantAilleurs->organisation->nom,
                    'recommandation' => 'Fournir justificatif de démission ou exclure de la liste'
                ];
            } else {
                $anomalies['majeures'][] = [
                    'code' => 'membre_existant',
                    'message' => 'Déjà membre de: ' . $existantAilleurs->organisation->nom,
                    'recommandation' => 'Vérifier la compatibilité des adhésions'
                ];
            }
        }

        // Vérifier professions exclues pour parti politique
        if ($typeOrganisation === 'parti_politique' && !empty($adherentData['profession'])) {
            $professionsExclues = $this->getProfessionsExcluesParti();
            if (in_array(strtolower($adherentData['profession']), array_map('strtolower', $professionsExclues))) {
                $anomalies['critiques'][] = [
                    'code' => 'profession_exclue_parti',
                    'message' => 'Profession exclue pour les partis politiques: ' . $adherentData['profession'],
                    'recommandation' => 'Exclure cette personne ou corriger la profession'
                ];
            }
        }

        // Anomalies majeures
        if (!empty($adherentData['telephone']) && !preg_match('/^[0-9]{8,9}$/', $adherentData['telephone'])) {
            $anomalies['majeures'][] = [
                'code' => 'telephone_invalide',
                'message' => 'Format de téléphone incorrect',
                'recommandation' => 'Utiliser le format gabonais (8-9 chiffres)'
            ];
        }

        if (!empty($adherentData['email']) && !filter_var($adherentData['email'], FILTER_VALIDATE_EMAIL)) {
            $anomalies['majeures'][] = [
                'code' => 'email_invalide',
                'message' => 'Format d\'email incorrect',
                'recommandation' => 'Corriger l\'adresse email'
            ];
        }

        // Anomalies mineures
        if (empty($adherentData['telephone']) && empty($adherentData['email'])) {
            $anomalies['mineures'][] = [
                'code' => 'contact_incomplet',
                'message' => 'Aucun moyen de contact fourni',
                'recommandation' => 'Ajouter téléphone ou email'
            ];
        }

        return $anomalies;
    }

    /**
     * Générer un numéro de récépissé unique
     */
    private function generateRecepisseNumber($type)
    {
        $prefixes = [
            'parti_politique' => 'PP',
            'association' => 'AS',
            'ong' => 'ONG',
            'confession_religieuse' => 'CR'
        ];

        $prefix = $prefixes[$type] ?? 'ORG';
        $year = date('Y');

        $count = Organisation::where('type', $type)
            ->where('numero_recepisse', 'LIKE', "REC-{$prefix}-{$year}-%")
            ->count();

        $sequence = str_pad($count + 1, 5, '0', STR_PAD_LEFT);

        return "REC-{$prefix}-{$year}-{$sequence}";
    }

    /**
     * Traiter les uploads de documents
     */
    private function handleDocumentUploads(Request $request, Dossier $dossier)
    {
        $uploadedFiles = [];

        // Format 1 : documents[{docTypeId}] (envoyé par le JS du formulaire de création)
        if ($request->hasFile('documents')) {
            $documents = $request->file('documents');
            foreach ($documents as $docTypeId => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                    $uploadedFiles[] = $this->processDocumentUpload($file, $dossier, $docTypeId);
                }
            }
        }

        // Format 2 : document_{type} (ancien format, rétro-compatibilité)
        foreach ($request->allFiles() as $fieldName => $files) {
            if (strpos($fieldName, 'document_') === 0) {
                $documentType = str_replace('document_', '', $fieldName);

                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                        $uploadedFiles[] = $this->processDocumentUpload($file, $dossier, $documentType);
                    }
                }
            }
        }

        return $uploadedFiles;
    }

    /**
     * Traiter l'upload d'un document individuel
     */
    private function processDocumentUpload(\Illuminate\Http\UploadedFile $file, Dossier $dossier, $documentType): array
    {
        $timestamp = time();
        $filename = $timestamp . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

        $path = $file->storeAs('documents/organisations', $filename, 'public');
        $hashFichier = hash_file('sha256', $file->getPathname());

        \App\Models\Document::create([
            'dossier_id' => $dossier->id,
            'document_type_id' => $this->getDocumentTypeId($documentType),
            'nom_fichier' => $filename,
            'nom_original' => $file->getClientOriginalName(),
            'chemin_fichier' => $path,
            'type_mime' => $file->getMimeType(),
            'taille' => $file->getSize(),
            'hash_fichier' => $hashFichier,
            'uploaded_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return [
            'nom_fichier' => $filename,
            'nom_original' => $file->getClientOriginalName(),
            'chemin' => $path,
            'type' => $documentType
        ];
    }

    /**
     * Générer l'accusé de réception PDF
     */
    private function generateAccuseReception(Dossier $dossier, Organisation $organisation, $user)
    {
        try {
            $data = [
                'dossier' => $dossier,
                'organisation' => $organisation,
                'user' => $user,
                'date_generation' => now(),
                'numero_recepisse' => $organisation->numero_recepisse
            ];

            $filename = 'accuse_reception_' . $dossier->numero_dossier . '_' . time() . '.pdf';
            $storagePath = 'accuses_reception/' . $filename;
            $fullPath = storage_path('app/public/' . $storagePath);

            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $htmlContent = $this->generateAccuseReceptionHTML($data);
            file_put_contents($fullPath, $htmlContent);

            \App\Models\Document::create([
                'dossier_id' => $dossier->id,
                'document_type_id' => 99,
                'nom_fichier' => $filename,
                'nom_original' => 'Accusé de réception',
                'chemin_fichier' => $storagePath,
                'type_mime' => 'application/pdf',
                'taille' => strlen($htmlContent),
                'hash_fichier' => hash('sha256', $htmlContent),
                'is_system_generated' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return $storagePath;

        } catch (\Exception $e) {
            \Log::error('Erreur génération accusé de réception v3: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reconstituer les données de confirmation depuis la base de données
     */
    private function reconstructConfirmationData(Dossier $dossier)
    {
        $donneesSupplementaires = [];

        // Décoder les données JSON de manière sécurisée
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }

        $adherentsStats = $this->calculateAdherentsStats($dossier);

        return [
            'adherents_stats' => $adherentsStats,
            'anomalies' => $donneesSupplementaires['adherents_anomalies'] ?? []
        ];
    }

    /**
     * Calculer les statistiques des adhérents depuis la base
     */
    private function calculateAdherentsStats(Dossier $dossier)
    {
        $organisation = $dossier->organisation;

        $totalAdherents = $organisation->adherents()->count();
        $adherentsValides = $organisation->adherents()->where('is_active', true)->count();

        $donneesSupplementaires = [];

        // Décoder les données JSON de manière sécurisée
        if (!empty($dossier->donnees_supplementaires)) {
            if (is_string($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = json_decode($dossier->donnees_supplementaires, true) ?? [];
            } elseif (is_array($dossier->donnees_supplementaires)) {
                $donneesSupplementaires = $dossier->donnees_supplementaires;
            }
        }

        $anomalies = $donneesSupplementaires['adherents_anomalies'] ?? [];

        $anomaliesCritiques = 0;
        $anomaliesMajeures = 0;
        $anomaliesMineures = 0;

        foreach ($anomalies as $anomalie) {
            $anomaliesAdherent = $anomalie['anomalies'] ?? [];

            if (!empty($anomaliesAdherent['critiques'])) {
                $anomaliesCritiques++;
            }
            if (!empty($anomaliesAdherent['majeures'])) {
                $anomaliesMajeures++;
            }
            if (!empty($anomaliesAdherent['mineures'])) {
                $anomaliesMineures++;
            }
        }

        return [
            'total' => $totalAdherents,
            'valides' => $adherentsValides,
            'anomalies_critiques' => $anomaliesCritiques,
            'anomalies_majeures' => $anomaliesMajeures,
            'anomalies_mineures' => $anomaliesMineures
        ];
    }

    /**
     * Obtenir le chemin de l'accusé de réception
     */
    private function getAccuseReceptionPath(Dossier $dossier)
    {
        $accuseDocument = $dossier->documents()
            ->where('nom_fichier', 'LIKE', 'accuse_reception_%')
            ->latest()
            ->first();

        if ($accuseDocument) {
            return storage_path('app/public/' . $accuseDocument->chemin_fichier);
        }

        return null;
    }

    /**
     * Obtenir l'ID du type de document
     */
    private function getDocumentTypeId($documentType)
    {
        $documentTypeMapping = [
            'statuts' => 1,
            'pv_ag' => 2,
            'liste_fondateurs' => 3,
            'justificatif_siege' => 4,
            'programme_politique' => 5,
            'doctrine_religieuse' => 6,
            'cv_dirigeants' => 7,
            'budget_previsionnel' => 8
        ];

        return $documentTypeMapping[$documentType] ?? 1;
    }

    /**
     * Liste des professions exclues pour les partis politiques
     */
    private function getProfessionsExcluesParti()
    {
        return [
            'Magistrat',
            'Juge',
            'Procureur',
            'Commissaire de police',
            'Officier de police judiciaire',
            'Militaire en activité',
            'Gendarme en activité',
            'Fonctionnaire de la sécurité d\'État',
            'Agent des services de renseignement',
            'Diplomate en mission',
            'Gouverneur de province',
            'Préfet',
            'Sous-préfet',
            'Maire en exercice',
            'Membre du Conseil constitutionnel',
            'Membre de la Cour de cassation',
            'Membre du Conseil d\'État',
            'Contrôleur général d\'État',
            'Inspecteur général d\'État',
            'Agent comptable de l\'État',
            'Trésorier payeur général',
            'Receveur des finances'
        ];
    }

    /**
     * Générer le contenu HTML de l'accusé de réception
     */
    private function generateAccuseReceptionHtml($data)
    {
        $content = '
        <h2 style="text-align: center; color: #006633;">ACCUSÉ DE RÉCEPTION</h2>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Informations du dossier</h3>
            <p><strong>Numéro de dossier:</strong> ' . $data['dossier']->numero_dossier . '</p>
            <p><strong>Numéro de récépissé:</strong> ' . $data['numero_recepisse'] . '</p>
            <p><strong>Date de soumission:</strong> ' . $data['dossier']->submitted_at->format('d/m/Y à H:i') . '</p>
            <p><strong>Type d\'organisation:</strong> ' . ucfirst(str_replace('_', ' ', $data['organisation']->type)) . '</p>
        </div>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Organisation</h3>
            <p><strong>Nom:</strong> ' . $data['organisation']->nom . '</p>
            <p><strong>Sigle:</strong> ' . ($data['organisation']->sigle ?? 'Non renseigné') . '</p>
            <p><strong>Province:</strong> ' . $data['organisation']->province . '</p>
        </div>
        
        <div class="info-box" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0;">
            <h3>Prochaines étapes</h3>
            <p>1. Votre dossier sera examiné dans l\'ordre d\'arrivée (système FIFO)</p>
            <p>2. Un agent sera assigné sous 48h ouvrées</p>
            <p>3. Vous serez notifié de l\'évolution par email</p>
            <p>4. Délai de traitement estimé: 72 heures ouvrées</p>
        </div>
        
        <div class="footer" style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
            <p>Document généré automatiquement le ' . $data['date_generation']->format('d/m/Y à H:i') . '</p>
            <p>Plateforme Numérique Gabonaise de Déclaration des Intentions (PNGDI)</p>
        </div>';

        return \App\Helpers\PdfTemplateHelper::wrapContent(
            'Accusé de Réception - ' . $data['dossier']->numero_dossier,
            $content
        );
    }

    /**
     * Obtenir le contenu du guide pour un type d'organisation
     */
    private function getGuideContent($type)
    {
        $guides = [
            'parti' => [
                'title' => 'Guide de création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 50 adhérents fondateurs',
                    'Présence dans au moins 3 provinces',
                    'Programme politique détaillé',
                    'Statuts conformes à la législation'
                ],
                'documents' => [
                    'Statuts signés et légalisés',
                    'Programme politique',
                    'Liste des fondateurs avec NIP',
                    'Procès-verbal de l\'assemblée constitutive'
                ]
            ],
            'parti_politique' => [
                'title' => 'Guide de création d\'un parti politique',
                'description' => 'Étapes nécessaires pour créer un parti politique au Gabon',
                'requirements' => [
                    'Minimum 50 adhérents fondateurs',
                    'Présence dans au moins 3 provinces',
                    'Programme politique détaillé',
                    'Statuts conformes à la législation'
                ],
                'documents' => [
                    'Statuts signés et légalisés',
                    'Programme politique',
                    'Liste des fondateurs avec NIP',
                    'Procès-verbal de l\'assemblée constitutive'
                ]
            ],
            'confession' => [
                'title' => 'Guide de création d\'une confession religieuse',
                'description' => 'Procédure pour l\'enregistrement d\'une confession religieuse',
                'requirements' => [
                    'Minimum 100 fidèles fondateurs',
                    'Doctrine religieuse clairement définie',
                    'Lieu de culte identifié',
                    'Responsables spirituels qualifiés'
                ],
                'documents' => [
                    'Statuts de la confession',
                    'Doctrine religieuse',
                    'Liste des fidèles fondateurs',
                    'Attestation du lieu de culte'
                ]
            ],
            'confession_religieuse' => [
                'title' => 'Guide de création d\'une confession religieuse',
                'description' => 'Procédure pour l\'enregistrement d\'une confession religieuse',
                'requirements' => [
                    'Minimum 100 fidèles fondateurs',
                    'Doctrine religieuse clairement définie',
                    'Lieu de culte identifié',
                    'Responsables spirituels qualifiés'
                ],
                'documents' => [
                    'Statuts de la confession',
                    'Doctrine religieuse',
                    'Liste des fidèles fondateurs',
                    'Attestation du lieu de culte'
                ]
            ],
            'association' => [
                'title' => 'Guide de création d\'une association',
                'description' => 'Formalités pour créer une association au Gabon',
                'requirements' => [
                    'Minimum 7 membres fondateurs',
                    'Objet social déterminé',
                    'Siège social au Gabon',
                    'Statuts conformes'
                ],
                'documents' => [
                    'Statuts de l\'association',
                    'Liste des membres fondateurs',
                    'Procès-verbal de l\'assemblée générale constitutive',
                    'Justificatif du siège social'
                ]
            ],
            'ong' => [
                'title' => 'Guide de création d\'une ONG',
                'description' => 'Procédure d\'enregistrement d\'une organisation non gouvernementale',
                'requirements' => [
                    'Minimum 10 membres fondateurs',
                    'Mission d\'intérêt général',
                    'Capacité d\'intervention',
                    'Transparence financière'
                ],
                'documents' => [
                    'Statuts de l\'ONG',
                    'Plan d\'action et budget prévisionnel',
                    'CV des dirigeants',
                    'Lettres d\'engagement des partenaires'
                ]
            ]
        ];

        return $guides[$type] ?? $guides['association'];
    }

    /**
     * Obtenir les documents requis selon le type d'organisation
     */
    private function getRequiredDocuments($type)
    {
        $baseDocuments = [
            'statuts' => ['name' => 'Statuts', 'required' => true],
            'pv_ag' => ['name' => 'PV Assemblée Générale', 'required' => true],
            'liste_fondateurs' => ['name' => 'Liste des fondateurs', 'required' => true],
            'justificatif_siege' => ['name' => 'Justificatif siège social', 'required' => false],
        ];

        switch ($type) {
            case 'parti':
            case 'parti_politique':
                $baseDocuments['programme_politique'] = ['name' => 'Programme politique', 'required' => true];
                $baseDocuments['cv_dirigeants'] = ['name' => 'CV des dirigeants', 'required' => true];
                break;

            case 'confession':
            case 'confession_religieuse':
                $baseDocuments['doctrine_religieuse'] = ['name' => 'Doctrine religieuse', 'required' => true];
                break;

            case 'ong':
                $baseDocuments['budget_previsionnel'] = ['name' => 'Budget prévisionnel', 'required' => true];
                $baseDocuments['cv_dirigeants'] = ['name' => 'CV des dirigeants', 'required' => true];
                break;
        }

        return $baseDocuments;
    }

    /**
     * Vérifier les documents requis
     */
    private function checkRequiredDocuments(Organisation $organisation)
    {
        $requiredDocs = $this->getRequiredDocuments($organisation->type);
        $uploadedDocs = $organisation->documents->pluck('type_document')->toArray();

        $missing = [];
        foreach ($requiredDocs as $key => $doc) {
            if ($doc['required'] && !in_array($key, $uploadedDocs)) {
                $missing[] = $doc['name'];
            }
        }

        return $missing;
    }

    /**
     * Obtenir la liste des provinces du Gabon
     */
    private function getProvinces()
    {
        return [
            'Estuaire' => 'Estuaire',
            'Haut-Ogooué' => 'Haut-Ogooué',
            'Moyen-Ogooué' => 'Moyen-Ogooué',
            'Ngounié' => 'Ngounié',
            'Nyanga' => 'Nyanga',
            'Ogooué-Ivindo' => 'Ogooué-Ivindo',
            'Ogooué-Lolo' => 'Ogooué-Lolo',
            'Ogooué-Maritime' => 'Ogooué-Maritime',
            'Woleu-Ntem' => 'Woleu-Ntem'
        ];
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Valider le format du NIP selon le nouveau standard gabonais
     * Format: XX-QQQQ-YYYYMMDD
     * 
     * @param string $nip
     * @return bool
     */
    private function validateNipFormat($nip)
    {
        if (empty($nip)) {
            return false;
        }

        // Vérification regex de base
        if (!preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', $nip)) {
            return false;
        }

        // Extraction des parties
        $parts = explode('-', $nip);
        if (count($parts) !== 3) {
            return false;
        }

        $prefix = $parts[0]; // XX (alphanumérique)
        $sequence = $parts[1]; // QQQQ (4 chiffres)
        $dateStr = $parts[2]; // YYYYMMDD (8 chiffres)

        // Validation prefix XX (2 caractères alphanumériques)
        if (!preg_match('/^[A-Z0-9]{2}$/', $prefix)) {
            return false;
        }

        // Validation sequence QQQQ (4 chiffres)
        if (!preg_match('/^[0-9]{4}$/', $sequence)) {
            return false;
        }

        // Validation date YYYYMMDD
        if (!preg_match('/^[0-9]{8}$/', $dateStr)) {
            return false;
        }

        // Extraction année, mois, jour
        $year = (int) substr($dateStr, 0, 4);
        $month = (int) substr($dateStr, 4, 2);
        $day = (int) substr($dateStr, 6, 2);

        // Validation date réelle
        if (!checkdate($month, $day, $year)) {
            return false;
        }

        // Validation plage d'années raisonnable (1900-2100)
        if ($year < 1900 || $year > 2100) {
            return false;
        }

        \Log::debug('NIP validé avec succès', [
            'nip' => $nip,
            'prefix' => $prefix,
            'sequence' => $sequence,
            'date' => sprintf('%04d-%02d-%02d', $year, $month, $day)
        ]);

        return true;
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Extraire la date de naissance depuis le NIP
     * 
     * @param string $nip
     * @return \Carbon\Carbon|null
     */
    private function extractBirthDateFromNip($nip)
    {
        if (!$this->validateNipFormat($nip)) {
            return null;
        }

        $parts = explode('-', $nip);
        $dateStr = $parts[2]; // YYYYMMDD

        $year = substr($dateStr, 0, 4);
        $month = substr($dateStr, 4, 2);
        $day = substr($dateStr, 6, 2);

        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
        } catch (\Exception $e) {
            return null;
        }
    }


    // =============================================
// 🔧 NOUVELLES ROUTES API POUR VALIDATION TEMPS RÉEL
// =============================================

    /**
     * ✅ NOUVELLE ROUTE API : Validation NIP en temps réel
     * POST /api/v1/validate-nip
     */
    public function validateNipApi(Request $request)
    {
        try {
            $request->validate([
                'nip' => 'required|string|max:20'
            ]);

            $nip = $request->input('nip');
            $isValid = $this->validateNipFormat($nip);

            $response = [
                'success' => true,
                'valid' => $isValid,
                'nip' => $nip,
                'format_expected' => 'XX-QQQQ-YYYYMMDD'
            ];

            if ($isValid) {
                // Extraire informations du NIP
                $birthDate = $this->extractBirthDateFromNip($nip);
                if ($birthDate) {
                    $response['birth_date'] = $birthDate->format('Y-m-d');
                    $response['age'] = $birthDate->diffInYears(now());

                    // Validation âge
                    if ($response['age'] < 18) {
                        $response['valid'] = false;
                        $response['message'] = 'Personne mineure détectée (âge: ' . $response['age'] . ' ans)';
                        $response['error_code'] = 'UNDERAGE';
                    } elseif ($response['age'] > 100) {
                        $response['warning'] = true;
                        $response['message'] = 'Âge suspect détecté (' . $response['age'] . ' ans)';
                    } else {
                        $response['message'] = 'NIP valide (âge: ' . $response['age'] . ' ans)';
                    }
                }

                // Vérifier si le NIP existe déjà
                if ($response['valid']) {
                    $exists = \App\Models\User::where('nip', $nip)->exists() ||
                        \App\Models\Adherent::where('nip', $nip)->exists() ||
                        \App\Models\Fondateur::where('nip', $nip)->exists();

                    $response['available'] = !$exists;

                    if ($exists) {
                        // Trouver où le NIP est utilisé
                        $usage = [];
                        if (\App\Models\User::where('nip', $nip)->exists()) {
                            $usage[] = 'utilisateur';
                        }
                        if (\App\Models\Adherent::where('nip', $nip)->exists()) {
                            $usage[] = 'adhérent';
                        }
                        if (\App\Models\Fondateur::where('nip', $nip)->exists()) {
                            $usage[] = 'fondateur';
                        }

                        $response['message'] = 'NIP déjà utilisé comme: ' . implode(', ', $usage);
                        $response['usage'] = $usage;
                    } else {
                        $response['message'] = 'NIP valide et disponible';
                    }
                }

            } else {
                $response['message'] = 'Format NIP invalide. Format attendu: XX-QQQQ-YYYYMMDD';
                $response['example'] = 'A1-2345-19901225';
                $response['help'] = [
                    'XX = 2 caractères alphanumériques (A-Z, 0-9)',
                    'QQQQ = 4 chiffres (0000-9999)',
                    'YYYYMMDD = Date de naissance (ex: 19901225 pour 25/12/1990)'
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Erreur validation NIP API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Erreur serveur lors de la validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NOUVELLE ROUTE API : Générer exemple de NIP valide
     * GET /api/v1/generate-nip-example
     */
    public function generateNipExample()
    {
        try {
            // Générer des exemples de NIP valides
            $examples = [];
            $prefixes = ['A1', 'B2', 'C3', '1A', '2B', '3C'];
            $sequences = ['0001', '1234', '5678', '9999'];

            foreach (range(1, 5) as $i) {
                $prefix = $prefixes[array_rand($prefixes)];
                $sequence = $sequences[array_rand($sequences)];

                // Date aléatoire entre 1960 et 2005
                $year = rand(1960, 2005);
                $month = rand(1, 12);
                $day = rand(1, 28); // Éviter les problèmes de jours invalides

                $dateStr = sprintf('%04d%02d%02d', $year, $month, $day);
                $example = $prefix . '-' . $sequence . '-' . $dateStr;

                $examples[] = [
                    'nip' => $example,
                    'prefix' => $prefix,
                    'sequence' => $sequence,
                    'birth_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                    'age' => now()->diffInYears(\Carbon\Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day)))
                ];
            }

            return response()->json([
                'success' => true,
                'examples' => $examples,
                'format' => 'XX-QQQQ-YYYYMMDD',
                'description' => [
                    'XX' => '2 caractères alphanumériques',
                    'QQQQ' => '4 chiffres',
                    'YYYYMMDD' => 'Date de naissance (ANNÉE MOIS JOUR)'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur génération exemples',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NOUVELLE ROUTE API : Validation de lot de NIP
     * POST /api/v1/validate-nip-batch
     */
    public function validateNipBatch(Request $request)
    {
        try {
            $request->validate([
                'nips' => 'required|array|max:100',
                'nips.*' => 'required|string|max:20'
            ]);

            $nips = $request->input('nips');
            $results = [];

            foreach ($nips as $index => $nip) {
                $isValid = $this->validateNipFormat($nip);

                $result = [
                    'index' => $index,
                    'nip' => $nip,
                    'valid' => $isValid
                ];

                if ($isValid) {
                    $birthDate = $this->extractBirthDateFromNip($nip);
                    if ($birthDate) {
                        $result['age'] = $birthDate->diffInYears(now());
                        $result['birth_date'] = $birthDate->format('Y-m-d');
                    }

                    // Vérifier existence
                    $exists = \App\Models\User::where('nip', $nip)->exists() ||
                        \App\Models\Adherent::where('nip', $nip)->exists() ||
                        \App\Models\Fondateur::where('nip', $nip)->exists();

                    $result['available'] = !$exists;
                } else {
                    $result['message'] = 'Format invalide';
                }

                $results[] = $result;
            }

            // Statistiques - SYNTAXE CORRIGÉE
            $validResults = array_filter($results, function ($r) {
                return $r['valid'];
            });
            $invalidResults = array_filter($results, function ($r) {
                return !$r['valid'];
            });
            $availableResults = array_filter($results, function ($r) {
                return isset($r['valid']) && $r['valid'] && isset($r['available']) && $r['available'];
            });
            $duplicateResults = array_filter($results, function ($r) {
                return isset($r['valid']) && $r['valid'] && isset($r['available']) && !$r['available'];
            });

            $stats = [
                'total' => count($results),
                'valid' => count($validResults),
                'invalid' => count($invalidResults),
                'available' => count($availableResults),
                'duplicates' => count($duplicateResults)
            ];

            return response()->json([
                'success' => true,
                'results' => $results,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur validation batch',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * ✅ NOUVELLE MÉTHODE : Valider données organisation (réutilise logique existante)
     */
    private function validateOrganisationData(array $organisationData, Request $request)
    {
        // Créer une nouvelle request avec les données filtrées
        $filteredRequest = new Request($organisationData);
        $filteredRequest->setUserResolver($request->getUserResolver());
        $filteredRequest->setRouteResolver($request->getRouteResolver());

        // Réutiliser la validation existante SANS les adhérents
        return $this->validateCompleteOrganisationDataWithoutAdherents($filteredRequest);
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Validation sans adhérents (adaptation de l'existante)
     */
    /**
     * ✅ CORRECTION : Validation adaptée pour Phase 1 (sans adhérents)
     */
    /**
     * ✅ SOLUTION ÉLÉGANTE : Validation avec fondateurs comme adhérents Phase 1
     */
    /**
     * ✅ ÉTAPE 1 : REMPLACEMENT COMPLET
     * Remplacer TOUTE la méthode validateCompleteOrganisationDataWithoutAdherents()
     */
    private function validateCompleteOrganisationDataWithoutAdherents(Request $request)
    {
        // Créer une copie des données de la request
        $allData = $request->all();

        \Log::info('🔍 DÉBUT VALIDATION PHASE 1', [
            'has_fondateurs' => isset($allData['fondateurs']),
            'fondateurs_count' => is_array($allData['fondateurs'] ?? null) ? count($allData['fondateurs']) : 'not_array',
            'has_adherents' => isset($allData['adherents']),
            'adherents_count' => is_array($allData['adherents'] ?? null) ? count($allData['adherents']) : 'not_array'
        ]);

        // ✅ SOLUTION ÉLÉGANTE : Utiliser les fondateurs comme adhérents initiaux
        $fondateurs = $allData['fondateurs'] ?? [];

        // Décoder les fondateurs si c'est du JSON
        if (is_string($fondateurs)) {
            $fondateurs = json_decode($fondateurs, true) ?? [];
        }

        // ✅ VÉRIFICATION : S'assurer qu'on a des fondateurs
        if (empty($fondateurs) || !is_array($fondateurs)) {
            \Log::error('❌ AUCUN FONDATEUR FOURNI POUR PHASE 1', [
                'fondateurs_raw' => $allData['fondateurs'] ?? 'null',
                'is_array' => is_array($fondateurs)
            ]);
            throw new \Illuminate\Validation\ValidationException(
                Validator::make([], [])
                    ->after(function ($validator) {
                        $validator->errors()->add('fondateurs', 'Au moins un fondateur est requis pour créer l\'organisation.');
                    })
            );
        }

        // ✅ CONVERSION FONDATEURS → ADHÉRENTS
        $adherentsFromFondateurs = [];
        foreach ($fondateurs as $index => $fondateur) {
            $adherentsFromFondateurs[] = [
                'nip' => $fondateur['nip'] ?? '',
                'nom' => $fondateur['nom'] ?? '',
                'prenom' => $fondateur['prenom'] ?? '',
                'fonction' => $fondateur['fonction'] ?? 'Fondateur',
                'telephone' => $fondateur['telephone'] ?? '',
                'email' => $fondateur['email'] ?? '',
                'profession' => $fondateur['profession'] ?? 'Dirigeant', // ✅ Valeur par défaut
                'civilite' => $fondateur['civilite'] ?? 'M'
            ];
        }

        // ✅ REMPLACER les adhérents par les fondateurs convertis
        $allData['adherents'] = $adherentsFromFondateurs;

        \Log::info('✅ CONVERSION FONDATEURS→ADHÉRENTS EFFECTUÉE', [
            'fondateurs_input' => count($fondateurs),
            'adherents_generated' => count($adherentsFromFondateurs),
            'sample_adherent' => $adherentsFromFondateurs[0] ?? null
        ]);

        // ✅ MARQUER COMME PHASE 1 (pour validation différenciée)
        $allData['__phase_1_validation'] = true;

        // Créer une nouvelle request temporaire avec toutes les données
        $tempRequest = new Request($allData);
        $tempRequest->setUserResolver($request->getUserResolver());
        $tempRequest->setRouteResolver($request->getRouteResolver());

        // ✅ RÉCUPÉRER LE TYPE
        $type = $request->input('type_organisation');

        \Log::info('🎯 APPEL VALIDATION AVEC PARAMÈTRES', [
            'type' => $type,
            'phase_1' => true,
            'fondateurs_as_adherents' => count($adherentsFromFondateurs)
        ]);

        try {
            // ✅ UTILISER LA MÉTHODE DE VALIDATION EXISTANTE AVEC LES 2 PARAMÈTRES
            $validatedData = $this->validateCompleteOrganisationData($tempRequest, $type);

            \Log::info('✅ VALIDATION PHASE 1 RÉUSSIE', [
                'validated_fields' => array_keys($validatedData),
                'fondateurs_validated' => count($validatedData['fondateurs'] ?? []),
                'adherents_validated' => count($validatedData['adherents'] ?? [])
            ]);

            return $validatedData;

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR VALIDATION PHASE 1', [
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }



    /**
     * ✅ AMÉLIORATION : Intégration avec ChunkingController pour INSERTION DURING CHUNKING
     * Version corrigée avec gestion des erreurs et statistiques
     */
    private function processWithInsertionDuringChunking(array $adherentsArray, $organisation, $dossier)
    {
        $startTime = microtime(true);

        // Préparer les chunks pour insertion immédiate
        $chunkSize = 500;
        $chunks = array_chunk($adherentsArray, $chunkSize);
        $totalChunks = count($chunks);

        Log::info('🚀 DÉMARRAGE INSERTION DURING CHUNKING', [
            'total_adherents' => count($adherentsArray),
            'total_chunks' => $totalChunks,
            'chunk_size' => $chunkSize,
            'solution' => 'INSERTION_DURING_CHUNKING'
        ]);

        // ✅ UTILISER LE ChunkingController pour insertion immédiate
        $chunkingController = app(\App\Http\Controllers\Operator\ChunkingController::class);

        $totalInserted = 0;
        $allErrors = [];
        $chunksProcessed = 0;

        DB::beginTransaction();

        try {
            foreach ($chunks as $index => $chunk) {
                $chunkData = [
                    'dossier_id' => $dossier->id,
                    'adherents' => $chunk,
                    'chunk_index' => $index,
                    'total_chunks' => $totalChunks,
                    'is_final_chunk' => ($index === $totalChunks - 1)
                ];

                Log::info("🔄 TRAITEMENT CHUNK $index/$totalChunks", [
                    'chunk_size' => count($chunk),
                    'dossier_id' => $dossier->id
                ]);

                // ✅ INSERTION IMMÉDIATE via ChunkingController
                $fakeRequest = new \Illuminate\Http\Request($chunkData);
                $fakeRequest->setUserResolver(request()->getUserResolver());

                $result = $chunkingController->processChunk($fakeRequest);

                if ($result->getStatusCode() === 200) {
                    $data = json_decode($result->getContent(), true);
                    $inserted = $data['inserted'] ?? 0;
                    $totalInserted += $inserted;
                    $chunksProcessed++;

                    Log::info("✅ CHUNK $index INSÉRÉ AVEC SUCCÈS", [
                        'inserted' => $inserted,
                        'total_so_far' => $totalInserted
                    ]);
                } else {
                    $errorData = json_decode($result->getContent(), true);
                    $errorMessage = $errorData['message'] ?? "Erreur chunk $index";
                    $allErrors[] = $errorMessage;

                    Log::error("❌ ERREUR CHUNK $index", [
                        'error' => $errorMessage,
                        'status_code' => $result->getStatusCode()
                    ]);
                }
            }

            DB::commit();

            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime, 2);

            Log::info('🎉 INSERTION DURING CHUNKING TERMINÉE', [
                'total_inserted' => $totalInserted,
                'chunks_processed' => $chunksProcessed,
                'errors_count' => count($allErrors),
                'processing_time_seconds' => $processingTime,
                'solution' => 'INSERTION_DURING_CHUNKING'
            ]);

            return [
                'success' => empty($allErrors) || $totalInserted > 0,

                // ✅ FORMAT COMPATIBLE avec l'interface chunking existante
                'data' => [
                    'processed' => $totalInserted,
                    'imported' => $totalInserted,
                    'errors' => count($allErrors),
                    'error_details' => $allErrors,
                    'chunks_processed' => $chunksProcessed
                ],

                // ✅ MAINTENIR aussi l'ancien format pour la rétrocompatibilité
                'total_inserted' => $totalInserted,
                'chunks_processed' => $chunksProcessed,
                'errors' => $allErrors,
                'processing_time' => $processingTime . ' secondes',
                'solution' => 'INSERTION_DURING_CHUNKING'
            ];

        } catch (\Exception $e) {
            DB::rollback();

            Log::error('❌ ERREUR CRITIQUE INSERTION DURING CHUNKING', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'total_inserted_before_error' => $totalInserted
            ]);

            return [
                'success' => false,
                'total_inserted' => $totalInserted,
                'chunks_processed' => $chunksProcessed,
                'errors' => array_merge($allErrors, [$e->getMessage()]),
                'solution' => 'INSERTION_DURING_CHUNKING'
            ];
        }
    }



}