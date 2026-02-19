<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Adherent;
use App\Models\Fondateur;
use App\Services\AdherentImportService;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Models\AdherentAnomalie;
use App\Models\InscriptionLink;

use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\NipValidationService;

class AdherentController extends Controller
{
    protected $importService;
    protected $fileUploadService;
    protected $notificationService;
    
    public function __construct(
        AdherentImportService $importService,
        FileUploadService $fileUploadService,
        NotificationService $notificationService
    ) {
        $this->importService = $importService;
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Liste des adhérents d'une organisation
     */
    public function index(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $adherents = $organisation->adherents()
            ->with('exclusion')
            ->paginate(20);
        
        $stats = [
            'total' => $organisation->adherents()->count(),
            'actifs' => $organisation->adherentsActifs()->count(),
            'inactifs' => $organisation->adherents()->where('is_active', false)->count(),
            'fondateurs' => $organisation->fondateurs()->count()
        ];
        
        return view('operator.adherents.index', compact('organisation', 'adherents', 'stats'));
    }
    
    /**
     * Formulaire d'ajout d'un adhérent
     */
    public function create(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Vérifier que l'organisation peut ajouter des adhérents
        if (!$organisation->canAddAdherent()) {
            return redirect()->route('operator.adherents.index', $organisation)
                ->with('error', 'Cette organisation ne peut pas ajouter d\'adhérents dans son état actuel');
        }
        
        return view('operator.adherents.create', compact('organisation'));
    }
    
    /**
     * Enregistrer un nouvel adhérent
     */
    /**
     * Enregistrer un nouvel adhérent avec gestion des anomalies NIP
     * Conforme à la règle métier SGLP : enregistrement non-bloquant
     */
    public function store(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        // === VALIDATION DE BASE (NON-BLOQUANTE POUR NIP) ===
        $validated = $request->validate([
            'nip' => 'nullable|string|max:20', // ✅ NIP non obligatoire
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100', 
            'date_naissance' => 'nullable|date|before:today', // ✅ Non obligatoire
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
            'nationalite' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:255', // ✅ Non obligatoire
            'adresse_complete' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'departement' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'fonction' => 'required|string|max:100',
            'motif_adhesion' => 'nullable|string|max:500',
            'piece_identite' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048'
        ]);

        try {
            // === DÉTECTION DES ANOMALIES ===
            $anomaliesResult = $this->detectAdherentAnomalies($validated, $organisation);
            
            // === DÉTERMINER LE STATUT SELON LES ANOMALIES ===
            $statutValidation = $this->determineStatutFromAnomalies($anomaliesResult['anomalies']);
            
            // === PRÉ-REMPLISSAGE DEPUIS BASE NIP (SI DISPONIBLE) ===
            if (!empty($validated['nip'])) {
                $nipValidationService = new \App\Services\NipValidationService();
                $nipValidation = $nipValidationService->validateNip(
                    $validated['nip'],
                    $validated['nom'],
                    $validated['prenom']
                );
                
                // Fusionner données NIP si disponibles (sans écraser les données saisies)
                if (isset($nipValidation['nip_data'])) {
                    $nipData = $nipValidation['nip_data'];
                    
                    // ✅ CORRECTION : Fusion intelligente sans écraser les données saisies
                    $fieldsToMerge = [
                        'date_naissance',
                        'lieu_naissance', 
                        'sexe',
                        'telephone',
                        'email'
                    ];
                    
                    foreach ($fieldsToMerge as $field) {
                        // Ne fusionner que si le champ est vide ou null dans les données saisies
                        if (empty($validated[$field]) && !empty($nipData[$field])) {
                            $validated[$field] = $nipData[$field];
                        }
                    }
                    
                    \Log::info('Fusion données NIP effectuée', [
                        'nip' => $validated['nip'],
                        'champs_fusionnes' => array_intersect_key($nipData, array_flip($fieldsToMerge))
                    ]);
                }
            }
            
            // === UPLOAD DES FICHIERS ===
            $pieceIdentitePath = null;
            if ($request->hasFile('piece_identite')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('piece_identite'),
                    'adherents/pieces_identite'
                );
                $pieceIdentitePath = $uploadResult['file_path'];
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('photo'),
                    'adherents/photos'
                );
                $photoPath = $uploadResult['file_path'];
            }

            // === CRÉER L'ADHÉRENT (TOUJOURS) ===
            $adherent = Adherent::create([
                'organisation_id' => $organisation->id,
                'nip' => $validated['nip'] ?? '',
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'date_naissance' => $validated['date_naissance'],
                'lieu_naissance' => $validated['lieu_naissance'],
                'sexe' => $validated['sexe'],
                'nationalite' => $validated['nationalite'] ?? 'Gabonaise',
                'profession' => $validated['profession'],
                'adresse_complete' => $validated['adresse_complete'],
                'province' => $validated['province'],
                'departement' => $validated['departement'],
                'telephone' => $validated['telephone'],
                'email' => $validated['email'],
                'fonction' => $validated['fonction'],
                'motif_adhesion' => $validated['motif_adhesion'],
                'piece_identite' => $pieceIdentitePath,
                'photo' => $photoPath,
                'date_adhesion' => now(),
                
                // === STATUT SELON ANOMALIES ===
                'statut_validation' => $statutValidation,
                'is_active' => $statutValidation !== 'en_attente',
                'has_anomalies' => !empty($anomaliesResult['anomalies']),
                'anomalies_severity' => $anomaliesResult['severity'],
                'anomalies_data' => !empty($anomaliesResult['anomalies']) ? $anomaliesResult['anomalies'] : null,
                
                // === APPARTENANCE MULTIPLE ===
                'appartenance_multiple' => in_array('DOUBLE_APPARTENANCE_PARTI', array_column($anomaliesResult['anomalies'], 'code')),
                'organisations_precedentes' => $anomaliesResult['organisations_precedentes'] ?? null,
                
                'nip_verified_at' => now()
            ]);
            
            // === CRÉER LES ENREGISTREMENTS D'ANOMALIES ===
            if (!empty($anomaliesResult['anomalies'])) {
                $this->createAnomalieRecords($adherent, $anomaliesResult['anomalies']);
            }
            
            // === MESSAGE DE SUCCÈS ADAPTÉ ===
            $message = $this->getSuccessMessage($statutValidation, $anomaliesResult);
            
            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error('Erreur création adhérent', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Détecter toutes les anomalies d'un adhérent
     */
    private function detectAdherentAnomalies(array $data, Organisation $organisation): array
    {
        $anomalies = [];
        $severity = null;
        $organisationsPrecedentes = null;
        
        $nip = $data['nip'] ?? '';
        $profession = $data['profession'] ?? '';
        $dateNaissance = $data['date_naissance'] ?? null;
        
        // === ANOMALIES CRITIQUES ===
        
        // 1. NIP absent/vide
        if (empty(trim($nip))) {
            $anomalies[] = [
                'code' => 'NIP_ABSENT',
                'type' => 'critique',
                'message' => 'NIP absent ou vide',
                'champ' => 'nip',
                'valeur' => $nip
            ];
            $severity = 'critique';
        } else {
            // 2. Format NIP incorrect (devient MAJEURE selon votre correction)
            if (!$this->validateNipFormat($nip)) {
                $anomalies[] = [
                    'code' => 'NIP_FORMAT_INVALIDE',
                    'type' => 'majeure',
                    'message' => 'Format NIP incorrect (attendu: XX-QQQQ-YYYYMMDD)',
                    'champ' => 'nip',
                    'valeur' => $nip
                ];
                if (!$severity) $severity = 'majeure';
            } else {
                // 3. Âge mineur (si NIP valide)
                $age = $this->extractAgeFromNip($nip);
                if ($age !== null && $age < 18) {
                    $anomalies[] = [
                        'code' => 'AGE_MINEUR',
                        'type' => 'critique',
                        'message' => "Personne mineure détectée (âge: {$age} ans)",
                        'champ' => 'nip',
                        'valeur' => $age
                    ];
                    $severity = 'critique';
                } elseif ($age !== null && $age > 100) {
                    $anomalies[] = [
                        'code' => 'AGE_SUSPECT',
                        'type' => 'majeure',
                        'message' => "Âge suspect détecté (âge: {$age} ans)",
                        'champ' => 'nip',
                        'valeur' => $age
                    ];
                    if (!$severity) $severity = 'majeure';
                }
            }
            
            // 4. Double appartenance parti politique
            if ($organisation->type === 'parti_politique') {
                $existingMembership = $this->checkExistingMembership($nip, $organisation->id);
                if ($existingMembership) {
                    $anomalies[] = [
                        'code' => 'DOUBLE_APPARTENANCE_PARTI',
                        'type' => 'critique',
                        'message' => 'Appartenance multiple à des partis politiques détectée',
                        'champ' => 'nip',
                        'valeur' => $existingMembership
                    ];
                    $organisationsPrecedentes = json_encode($existingMembership);
                    $severity = 'critique';
                }
            }
        }
        
        // 5. Profession interdite
        if (!empty($profession) && $this->isProfessionInterdite($profession)) {
            $anomalies[] = [
                'code' => 'PROFESSION_INTERDITE',
                'type' => 'critique',
                'message' => "Profession interdite: {$profession}",
                'champ' => 'profession',
                'valeur' => $profession
            ];
            $severity = 'critique';
        }
        
        // === ANOMALIES MAJEURES ===
        
        // 6. Date de naissance absente
        if (empty($dateNaissance)) {
            $anomalies[] = [
                'code' => 'DATE_NAISSANCE_ABSENTE',
                'type' => 'majeure',
                'message' => 'Date de naissance non renseignée',
                'champ' => 'date_naissance',
                'valeur' => null
            ];
            if (!$severity) $severity = 'majeure';
        } else {
            // 7. Date de naissance format non conforme
            if (!$this->validateDateFormat($dateNaissance)) {
                $anomalies[] = [
                    'code' => 'DATE_NAISSANCE_FORMAT_INVALIDE',
                    'type' => 'majeure',
                    'message' => 'Format de date de naissance invalide',
                    'champ' => 'date_naissance',
                    'valeur' => $dateNaissance
                ];
                if (!$severity) $severity = 'majeure';
            }
        }
        
        // === ANOMALIES MINEURES ===
        
        // 8. Téléphone invalide
        if (!empty($data['telephone']) && !$this->validatePhoneFormat($data['telephone'])) {
            $anomalies[] = [
                'code' => 'TELEPHONE_INVALIDE',
                'type' => 'mineure',
                'message' => 'Format de téléphone invalide',
                'champ' => 'telephone',
                'valeur' => $data['telephone']
            ];
            if (!$severity) $severity = 'mineure';
        }
        
        // 9. Profession manquante
        if (empty($profession)) {
            $anomalies[] = [
                'code' => 'PROFESSION_MANQUANTE',
                'type' => 'mineure',
                'message' => 'Profession non renseignée',
                'champ' => 'profession',
                'valeur' => null
            ];
            if (!$severity) $severity = 'mineure';
        }
        
        return [
            'anomalies' => $anomalies,
            'severity' => $severity,
            'organisations_precedentes' => $organisationsPrecedentes
        ];
    }


    /**
     * Déterminer le statut selon les anomalies
     */
    private function determineStatutFromAnomalies(array $anomalies): string
    {
        foreach ($anomalies as $anomalie) {
            if ($anomalie['type'] === 'critique') {
                return 'en_attente';
            }
        }
        return 'valide';
    }


    /**
     * Vérifier si une profession est interdite
     */
    private function isProfessionInterdite(string $profession): bool
    {
        $filePath = storage_path('app/config/professions_interdites.txt');
        
        if (!file_exists($filePath)) {
            // Créer le fichier avec des professions par défaut
            $this->createDefaultProfessionsInterdites($filePath);
        }
        
        $professionsInterdites = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $professionsInterdites = array_map('trim', $professionsInterdites);
        $professionsInterdites = array_filter($professionsInterdites, function($line) {
            return !empty($line) && !str_starts_with($line, '#');
        });
        
        $professionLower = strtolower(trim($profession));
        
        foreach ($professionsInterdites as $interdite) {
            if (strtolower(trim($interdite)) === $professionLower) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Créer le fichier par défaut des professions interdites
     */
    private function createDefaultProfessionsInterdites(string $filePath): void
    {
        $defaultContent = "# Professions interdites pour adhésion aux organisations
    # Une profession par ligne, case insensitive
    magistrat
    juge
    procureur
    commissaire de police
    gendarme
    militaire actif
    fonctionnaire sécurité
    agent secret
    prefet
    sous-prefet
    gouverneur
    ministre
    directeur general
    secretaire general
    ";
        
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        file_put_contents($filePath, $defaultContent);
    }


    /**
     * Valider le format NIP XX-QQQQ-YYYYMMDD
     */
    private function validateNipFormat(string $nip): bool
    {
        return preg_match('/^[A-Z0-9]{2}-[0-9]{4}-[0-9]{8}$/', trim($nip));
    }

    /**
     * Extraire l'âge depuis un NIP valide
     */
    private function extractAgeFromNip(string $nip): ?int
    {
        if (!$this->validateNipFormat($nip)) {
            return null;
        }
        
        $datePart = substr($nip, -8); // Derniers 8 chiffres: YYYYMMDD
        $year = substr($datePart, 0, 4);
        $month = substr($datePart, 4, 2);
        $day = substr($datePart, 6, 2);
        
        try {
            $birthDate = new \DateTime("{$year}-{$month}-{$day}");
            $now = new \DateTime();
            return $now->diff($birthDate)->y;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * Valider le format de date
     */
    private function validateDateFormat($date): bool
    {
        if (empty($date)) return false;
        
        try {
            $d = \DateTime::createFromFormat('Y-m-d', $date);
            return $d && $d->format('Y-m-d') === $date;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * Valider le format téléphone
     */
    private function validatePhoneFormat(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        return strlen($cleaned) >= 8 && strlen($cleaned) <= 15;
    }

    /**
     * Vérifier appartenance existante
     */
    private function checkExistingMembership(string $nip, int $currentOrgId): ?array
    {
        $existing = \App\Models\Adherent::where('nip', $nip)
            ->where('organisation_id', '!=', $currentOrgId)
            ->where('is_active', true)
            ->with('organisation')
            ->get();
        
        if ($existing->isEmpty()) {
            return null;
        }
        
        return $existing->map(function($adherent) {
            return [
                'id' => $adherent->organisation->id,
                'nom' => $adherent->organisation->nom,
                'type' => $adherent->organisation->type,
                'statut' => $adherent->organisation->statut
            ];
        })->toArray();
    }

    /**
     * Créer les enregistrements d'anomalies
     */
    private function createAnomalieRecords(Adherent $adherent, array $anomalies): void
    {
        foreach ($anomalies as $anomalie) {
            AdherentAnomalie::createFromAdherentData($adherent, $anomalie, 0);
        }
    }


    /**
     * Générer le message de succès selon le contexte
     */
    private function getSuccessMessage(string $statut, array $anomaliesResult): string
    {
        $baseMessage = 'Adhérent ajouté avec succès.';
        
        if (empty($anomaliesResult['anomalies'])) {
            return $baseMessage . ' Aucune anomalie détectée.';
        }
        
        $count = count($anomaliesResult['anomalies']);
        $severity = $anomaliesResult['severity'];
        
        if ($statut === 'en_attente') {
            return $baseMessage . " {$count} anomalie(s) {$severity}(s) détectée(s). Validation manuelle requise.";
        } else {
            return $baseMessage . " {$count} anomalie(s) détectée(s) mais adhérent activé. Correction recommandée.";
        }
    }


    /**
     * Afficher un adhérent
     */
    public function show(Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        $adherent->load(['histories', 'exclusion']);
        
        return view('operator.adherents.show', compact('organisation', 'adherent'));
    }

    /**
     * Formulaire de modification d'un adhérent
     */
    public function edit(Organisation $organisation, Adherent $adherent)
    {
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }

        return view('operator.adherents.edit', compact('organisation', 'adherent'));
    }

    /**
     * Mettre à jour un adhérent
     */
    public function update(Request $request, Organisation $organisation, Adherent $adherent)
    {
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nip' => 'nullable|string|max:20',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'nullable|date|before:today',
            'lieu_naissance' => 'nullable|string|max:255',
            'sexe' => 'nullable|in:M,F',
            'nationalite' => 'nullable|string|max:100',
            'profession' => 'nullable|string|max:255',
            'adresse_complete' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:100',
            'departement' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'fonction' => 'required|string|max:100',
            'piece_identite' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            // Upload des fichiers si fournis
            if ($request->hasFile('piece_identite')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('piece_identite'),
                    'adherents/pieces_identite'
                );
                $adherent->piece_identite = $uploadResult['file_path'];
            }

            if ($request->hasFile('photo')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('photo'),
                    'adherents/photos'
                );
                $adherent->photo = $uploadResult['file_path'];
            }

            $adherent->fill($validated);
            $adherent->save();

            // Recalculer les anomalies et synchroniser la table adherent_anomalies
            $adherent->detectAndManageAllAnomalies();
            $adherent->save();
            $adherent->syncAnomaliesTable();

            // Historique
            $adherent->addToHistorique('modification', 'Informations modifiées par l\'opérateur');

            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', 'Adhérent modifié avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur modification adhérent: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la modification.');
        }
    }

    /**
     * Démission / suppression douce d'un adhérent
     */
    public function destroy(Organisation $organisation, Adherent $adherent)
    {
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }

        if ($adherent->is_fondateur) {
            return back()->with('error', 'Un membre fondateur ne peut pas être retiré de cette manière.');
        }

        try {
            $adherent->update([
                'is_active' => false,
                'date_exclusion' => now(),
                'motif_exclusion' => 'Démission',
            ]);

            $adherent->addToHistorique('demission', 'Démission enregistrée par l\'opérateur');

            return redirect()->route('operator.adherents.index', $organisation)
                ->with('success', $adherent->nom . ' ' . $adherent->prenom . ' a été retiré(e) (démission).');
        } catch (\Exception $e) {
            Log::error('Erreur démission adhérent: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'enregistrement de la démission.');
        }
    }

    /**
     * Importer des adhérents via CSV
     */
    public function import(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        if ($request->isMethod('get')) {
            // Afficher le formulaire d'import
            $importHistory = $this->importService->getImportHistory($organisation);
            return view('operator.adherents.import', compact('organisation', 'importHistory'));
        }
        
        // Traiter l'import
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120'
        ]);
        
        try {
            $result = $this->importService->importFromCsv($organisation, $request->file('file'));
            
            // Notification
            $this->notificationService->notify(
                Auth::user(),
                'Import d\'adhérents terminé',
                sprintf(
                    'L\'import est terminé. %d adhérents importés avec succès, %d erreurs.',
                    $result['summary']['success'],
                    $result['summary']['errors']
                ),
                'info'
            );
            
            return redirect()->route('operator.adherents.import', $organisation)
                ->with('success', 'Import terminé')
                ->with('import_result', $result);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
    
    /**
     * Télécharger le modèle CSV
     */
    public function downloadTemplate()
    {
        $csvContent = $this->importService->generateTemplate();
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="modele_adherents.csv"'
        ]);
    }
    
    /**
     * Exporter les adhérents
     */
    public function export(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $filters = $request->only(['is_active', 'is_fondateur', 'search']);
        $csvContent = $this->importService->exportAdherents($organisation, $filters);
        
        $filename = sprintf(
            'adherents_%s_%s.csv',
            $organisation->sigle ?: 'export',
            date('Y-m-d')
        );
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    /**
     * Détecter les doublons
     */
    public function duplicates(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $duplicates = $this->importService->detectDuplicates($organisation);
        
        return view('operator.adherents.duplicates', compact('organisation', 'duplicates'));
    }
    
    /**
     * Exclure un adhérent
     */
    public function exclude(Request $request, Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'motif' => 'required|string|max:500',
            'date_exclusion' => 'nullable|date|before_or_equal:today',
            'document' => 'nullable|file|mimes:pdf|max:5120'
        ]);
        
        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $uploadResult = $this->fileUploadService->upload(
                    $request->file('document'),
                    'adherents/exclusions'
                );
                $documentPath = $uploadResult['file_path'];
            }
            
            $adherent->exclude(
                $validated['motif'],
                $validated['date_exclusion'] ?? now(),
                $documentPath
            );
            
            return redirect()->route('operator.adherents.index', $organisation)
                ->with('success', 'Adhérent exclu avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'exclusion : ' . $e->getMessage());
        }
    }
    
    /**
     * Réactiver un adhérent
     */
    public function reactivate(Request $request, Organisation $organisation, Adherent $adherent)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id() || $adherent->organisation_id !== $organisation->id) {
            abort(403);
        }
        
        // Vérifier que l'adhérent est inactif
        if ($adherent->is_active) {
            return redirect()->back()
                ->with('error', 'Cet adhérent est déjà actif');
        }
        
        $validated = $request->validate([
            'motif' => 'required|string|max:500'
        ]);
        
        try {
            $adherent->reactivate($validated['motif']);
            
            return redirect()->route('operator.adherents.show', [$organisation, $adherent])
                ->with('success', 'Adhérent réactivé avec succès');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la réactivation : ' . $e->getMessage());
        }
    }
    
    /**
     * Gérer les fondateurs
     */
    public function fondateurs(Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $fondateurs = $organisation->fondateurs()
            ->with('adherent')
            ->orderBy('ordre')
            ->get();
        
        return view('operator.adherents.fondateurs', compact('organisation', 'fondateurs'));
    }
    
    /**
     * Ajouter un fondateur
     */
    public function addFondateur(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'nip' => 'required|string|max:20',
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'sexe' => 'required|in:M,F',
            'nationalite' => 'required|string|max:100',
            'profession' => 'required|string|max:255',
            'adresse_complete' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'fonction' => 'required|string|max:100',
            'piece_identite' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);
        
        // Vérifier l'âge minimum (21 ans pour les fondateurs)
        $age = \Carbon\Carbon::parse($validated['date_naissance'])->age;
        if ($age < 21) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Un fondateur doit avoir au moins 21 ans');
        }
        
        DB::beginTransaction();
        
        try {
            // Upload de la pièce d'identité
            $uploadResult = $this->fileUploadService->upload(
                $request->file('piece_identite'),
                'fondateurs/pieces_identite'
            );
            
            // Créer le fondateur
            $fondateur = Fondateur::create(array_merge($validated, [
                'organisation_id' => $organisation->id,
                'piece_identite_path' => $uploadResult['file_path'],
                'ordre' => $organisation->fondateurs()->count() + 1,
                'is_active' => true
            ]));
            
            DB::commit();
            
            return redirect()->route('operator.adherents.fondateurs', $organisation)
                ->with('success', 'Fondateur ajouté avec succès');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de l\'ajout : ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le lien d'auto-enregistrement pour une organisation
     */
    public function generateRegistrationLink(Request $request, Organisation $organisation)
    {
        // Vérifier l'accès
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que l'organisation est approuvée
        if (!$organisation->isApprouvee()) {
            return response()->json([
                'success' => false,
                'message' => 'L\'organisation doit être approuvée pour générer un lien d\'enregistrement.'
            ], 403);
        }

        try {
            // Vérifier si un lien actif existe déjà
            $existingLink = $organisation->inscriptionLinks()
                ->where('is_active', true)
                ->first();

            if ($existingLink) {
                $pendingCount = $existingLink->getPendingCount();
                return response()->json([
                    'success' => true,
                    'data' => [
                        'url' => $existingLink->getPublicUrl(),
                        'token' => $existingLink->token,
                        'created_at' => $existingLink->created_at->format('d/m/Y H:i'),
                        'expires_at' => $existingLink->date_fin ? $existingLink->date_fin->format('d/m/Y H:i') : null,
                        'inscriptions' => $existingLink->inscriptions_actuelles,
                        'pending' => $pendingCount,
                        'is_existing' => true,
                    ]
                ]);
            }

            // Créer un nouveau lien
            $link = InscriptionLink::create([
                'organisation_id' => $organisation->id,
                'token' => InscriptionLink::generateUniqueToken(),
                'nom_campagne' => 'Adhésion en ligne - ' . $organisation->nom,
                'description' => 'Lien d\'auto-inscription pour les adhérents de ' . $organisation->nom,
                'date_debut' => now(),
                'date_fin' => now()->addMonths(6),
                'requiert_validation' => true,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $link->getPublicUrl(),
                    'token' => $link->token,
                    'created_at' => $link->created_at->format('d/m/Y H:i'),
                    'expires_at' => $link->date_fin->format('d/m/Y H:i'),
                    'inscriptions' => 0,
                    'pending' => 0,
                    'is_existing' => false,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur génération lien inscription', [
                'organisation_id' => $organisation->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Désactiver le lien d'inscription actif
     */
    public function deactivateRegistrationLink(Organisation $organisation)
    {
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }

        $link = $organisation->inscriptionLinks()->where('is_active', true)->first();

        if (!$link) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun lien actif trouvé.'
            ], 404);
        }

        $link->deactivate();

        return response()->json([
            'success' => true,
            'message' => 'Le lien d\'inscription a été désactivé.'
        ]);
    }

    /**
     * Afficher les inscriptions en attente de validation
     */
    public function pendingRegistrations(Organisation $organisation)
    {
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }

        $pendingAdherents = Adherent::where('organisation_id', $organisation->id)
            ->where('source_inscription', 'auto_inscription')
            ->where('statut_inscription', 'en_attente_validation')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'en_attente' => Adherent::where('organisation_id', $organisation->id)
                ->autoInscriptions()->enAttenteValidation()->count(),
            'validees' => Adherent::where('organisation_id', $organisation->id)
                ->autoInscriptions()->inscriptionsValidees()->count(),
            'rejetees' => Adherent::where('organisation_id', $organisation->id)
                ->autoInscriptions()->inscriptionsRejetees()->count(),
        ];

        return view('operator.adherents.pending-registrations', compact(
            'organisation', 'pendingAdherents', 'stats'
        ));
    }

    /**
     * Valider (approuver) une auto-inscription
     */
    public function validateRegistration(Request $request, Organisation $organisation, Adherent $adherent)
    {
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($adherent->organisation_id !== $organisation->id || $adherent->source_inscription !== 'auto_inscription') {
            abort(404);
        }

        $adherent->update([
            'statut_inscription' => 'validee',
            'is_active' => true,
            'validee_par' => Auth::id(),
            'validee_le' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'L\'inscription de ' . $adherent->nom . ' ' . $adherent->prenom . ' a été validée.'
            ]);
        }

        return redirect()->back()->with('success',
            'L\'inscription de ' . $adherent->nom . ' ' . $adherent->prenom . ' a été validée.');
    }

    /**
     * Rejeter une auto-inscription
     */
    public function rejectRegistration(Request $request, Organisation $organisation, Adherent $adherent)
    {
        if ($organisation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($adherent->organisation_id !== $organisation->id || $adherent->source_inscription !== 'auto_inscription') {
            abort(404);
        }

        $request->validate([
            'motif' => 'required|string|max:500',
        ], [
            'motif.required' => 'Veuillez indiquer le motif du rejet.',
        ]);

        $adherent->update([
            'statut_inscription' => 'rejetee',
            'is_active' => false,
            'motif_rejet_inscription' => $request->motif,
            'validee_par' => Auth::id(),
            'validee_le' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'L\'inscription de ' . $adherent->nom . ' ' . $adherent->prenom . ' a été rejetée.'
            ]);
        }

        return redirect()->back()->with('success',
            'L\'inscription de ' . $adherent->nom . ' ' . $adherent->prenom . ' a été rejetée.');
    }

   /**
 * Affiche la vue globale de tous les adhérents de l'utilisateur connecté
 * 
 * @return \Illuminate\View\View
 */
public function indexGlobal()
{
    try {
        // Récupérer toutes les organisations de l'utilisateur connecté avec leurs adhérents
        $organisations = auth()->user()->organisations()
            ->with(['adherents' => function($query) {
                $query->latest()->limit(5); // Limiter à 5 adhérents par organisation pour l'aperçu
            }])
            ->withCount('adherents') // Compter le total des adhérents
            ->get();

        // Statistiques globales
        $totalAdherents = auth()->user()->organisations()
            ->withCount('adherents')
            ->get()
            ->sum('adherents_count');

        $adherentsActifs = 0;
        $adherentsInactifs = 0;

        foreach ($organisations as $organisation) {
            foreach ($organisation->adherents as $adherent) {
                if ($adherent->is_active ?? true) {
                    $adherentsActifs++;
                } else {
                    $adherentsInactifs++;
                }
            }
        }

        return view('operator.members.index-global', compact(
            'organisations',
            'totalAdherents', 
            'adherentsActifs',
            'adherentsInactifs'
        ));

    } catch (\Exception $e) {
        \Log::error('Erreur dans indexGlobal AdherentController: ' . $e->getMessage());
        
        return redirect()->route('operator.dashboard')
            ->with('error', 'Erreur lors du chargement des adhérents. Veuillez réessayer.');
    }
}

}