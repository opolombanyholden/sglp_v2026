<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\OrganisationType;
use App\Models\OperationType;
use App\Models\WorkflowStep;
use App\Services\DocumentTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * CONTROLLER - GESTION DES TEMPLATES DE DOCUMENTS
 * 
 * Gère les modèles de documents officiels pour :
 * - Associations
 * - ONG
 * - Partis politiques
 * - Confessions religieuses
 * 
 * Projet : SGLP
 * CORRECTION : Utilisation des scopes appropriés et colonnes correctes
 */
class DocumentTemplateController extends Controller
{
    protected $templateService;

    public function __construct(DocumentTemplateService $templateService)
    {
        $this->templateService = $templateService;

        // ✅ CORRECTION : Middleware admin standard
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des templates
     */
    public function index(Request $request)
    {
        $query = DocumentTemplate::with(['organisationType', 'operationType', 'workflowStep'])
            ->withCount('generations');

        // Filtres
        if ($request->filled('organisation_type_id')) {
            $query->where('organisation_type_id', $request->organisation_type_id);
        }

        if ($request->filled('type_document')) {
            $query->where('type_document', $request->type_document);
        }

        if ($request->filled('auto_generate')) {
            $query->where('auto_generate', $request->auto_generate);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('nom', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Tri par défaut
        $query->orderBy('organisation_type_id')
            ->orderBy('nom');

        // Pagination
        $templates = $query->paginate(25);

        // Données pour les filtres
        $organisationTypes = OrganisationType::orderBy('nom')->get();
        $typesDocument = $this->getTypesDocument();

        // Statistiques
        $stats = [
            'total' => DocumentTemplate::count(),
            'active' => DocumentTemplate::where('is_active', true)->count(),
            'auto' => DocumentTemplate::where('auto_generate', true)->count(),
        ];

        return view('admin.document-templates.index', compact(
            'templates',
            'organisationTypes',
            'typesDocument',
            'stats'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        // ✅ CORRECTION : Utilisation du scope ordered() au lieu de orderBy('nom')
        $organisationTypes = OrganisationType::orderBy('nom')->get();
        $operationTypes = OperationType::ordered()->get();
        $typesDocument = $this->getTypesDocument();

        return view('admin.document-templates.create', compact(
            'organisationTypes',
            'operationTypes',
            'typesDocument'
        ));
    }

    /**
     * Enregistrer un nouveau template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:document_templates,code', 'regex:/^[A-Z0-9_]+$/'],
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'operation_type_id' => 'nullable|exists:operation_types,id',
            'workflow_step_id' => 'nullable|exists:workflow_steps,id',
            'type_document' => 'required|string|max:100',
            'template_path' => 'required|string|max:500',
            'layout_path' => 'nullable|string|max:500',
            'signature_image' => 'nullable|string|max:500',
            'header_text' => 'nullable|string',
            'signature_text' => 'nullable|string',
            'has_qr_code' => 'boolean',
            'has_watermark' => 'boolean',
            'has_signature' => 'boolean',
            'auto_generate' => 'boolean',
            'generation_delay_hours' => 'nullable|integer|min:0|max:720',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = $validated;
            $data['has_qr_code'] = $request->boolean('has_qr_code');
            $data['has_watermark'] = $request->boolean('has_watermark');
            $data['has_signature'] = $request->boolean('has_signature');
            $data['auto_generate'] = $request->boolean('auto_generate');
            $data['is_active'] = $request->boolean('is_active', true);

            $documentTemplate = DocumentTemplate::create($data);

            DB::commit();

            return redirect()
                ->route('admin.document-templates.show', $documentTemplate)
                ->with('success', 'Template créé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un template
     */
    public function show(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->load([
            'organisationType',
            'operationType',
            'workflowStep',
            'generations' => function ($query) {
                $query->latest()->limit(5);
            }
        ]);

        return view('admin.document-templates.show', compact('documentTemplate'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(DocumentTemplate $documentTemplate)
    {
        $organisationTypes = OrganisationType::orderBy('nom')->get();
        $operationTypes = OperationType::orderBy('libelle')->get();
        $typesDocument = DocumentTemplate::getTypesDocument();

        return view('admin.document-templates.edit', compact(
            'documentTemplate',
            'organisationTypes',
            'operationTypes',
            'typesDocument'
        ));
    }

    /**
     * Mettre à jour un template
     */
    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'regex:/^[A-Z0-9_]+$/', 'unique:document_templates,code,' . $documentTemplate->id],
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'organisation_type_id' => 'required|exists:organisation_types,id',
            'operation_type_id' => 'nullable|exists:operation_types,id',
            'workflow_step_id' => 'nullable|exists:workflow_steps,id',
            'type_document' => 'required|string|max:100',
            'template_path' => 'required|string|max:500',
            'layout_path' => 'nullable|string|max:500',
            'signature_image' => 'nullable|string|max:500',
            'header_text' => 'nullable|string',
            'signature_text' => 'nullable|string',
            'has_qr_code' => 'boolean',
            'has_watermark' => 'boolean',
            'has_signature' => 'boolean',
            'auto_generate' => 'boolean',
            'generation_delay_hours' => 'nullable|integer|min:0|max:720',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $data = $validated;
            $data['has_qr_code'] = $request->boolean('has_qr_code');
            $data['has_watermark'] = $request->boolean('has_watermark');
            $data['has_signature'] = $request->boolean('has_signature');
            $data['auto_generate'] = $request->boolean('auto_generate');
            $data['is_active'] = $request->boolean('is_active');

            $documentTemplate->update($data);

            DB::commit();

            return redirect()
                ->route('admin.document-templates.show', $documentTemplate)
                ->with('success', 'Template mis à jour avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un template
     */
    public function destroy(DocumentTemplate $documentTemplate)
    {
        // Vérifier s'il y a des documents générés
        $generationsCount = $documentTemplate->generations()->count();

        if ($generationsCount > 0) {
            return back()->with(
                'error',
                "Impossible de supprimer ce template : {$generationsCount} document(s) ont été générés avec."
            );
        }

        DB::beginTransaction();
        try {
            $documentTemplate->delete();

            DB::commit();

            return redirect()
                ->route('admin.document-templates.index')
                ->with('success', 'Template supprimé avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Designer WYSIWYG : publipostage - créer/configurer templates sans code
     */
    public function designer(DocumentTemplate $documentTemplate)
    {
        return view('admin.document-templates.designer', compact('documentTemplate'));
    }

    /**
     * Sauvegarder les modifications du designer (body HTML + variables + config page)
     */
    public function updateDesigner(Request $request, DocumentTemplate $documentTemplate)
    {
        // SÉCURITÉ : l'endpoint est déjà protégé par middleware admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Accès refusé.');
        }

        $validated = $request->validate([
            'body_content' => 'nullable|string|max:500000',
            'header_text' => 'nullable|string|max:50000',
            'signature_text' => 'nullable|string|max:50000',
            'variables' => 'nullable|array',
            'variables.*.key' => 'required|string|regex:/^[a-z0-9_]+$/|max:50',
            'variables.*.label' => 'required|string|max:255',
            'variables.*.type' => 'required|in:text,textarea,number,date,email,url',
            'variables.*.required' => 'nullable|boolean',
            'variables.*.default' => 'nullable|string|max:500',
            'page_config.format' => 'nullable|in:A4,A5,Letter,Legal',
            'page_config.orientation' => 'nullable|in:portrait,landscape',
            'page_config.margin_top' => 'nullable|integer|min:0|max:100',
            'page_config.margin_right' => 'nullable|integer|min:0|max:100',
            'page_config.margin_bottom' => 'nullable|integer|min:0|max:100',
            'page_config.margin_left' => 'nullable|integer|min:0|max:100',
            'has_qr_code' => 'nullable|boolean',
            'has_watermark' => 'nullable|boolean',
            'has_signature' => 'nullable|boolean',
        ], [
            'variables.*.key.regex' => 'La clé d\'une variable ne doit contenir que minuscules, chiffres et underscores.',
        ]);

        // Sanitization : retirer scripts, iframes, event handlers inline
        $sanitize = function (?string $html): ?string {
            if ($html === null || $html === '') return $html;
            // Retirer balises dangereuses
            $html = preg_replace('#<(script|iframe|object|embed|applet|meta|link|base|form)\b[^>]*>.*?</\1>#is', '', $html);
            $html = preg_replace('#<(script|iframe|object|embed|applet|meta|link|base|form)\b[^>]*/?\s*>#is', '', $html);
            // Retirer event handlers inline (onclick, onerror, etc.)
            $html = preg_replace('#\s+on[a-z]+\s*=\s*"[^"]*"#i', '', $html);
            $html = preg_replace("#\s+on[a-z]+\s*=\s*'[^']*'#i", '', $html);
            // Retirer javascript: URIs
            $html = preg_replace('#(href|src|action)\s*=\s*["\']\s*javascript:#i', '$1="#', $html);
            return $html;
        };

        try {
            $documentTemplate->body_content = $sanitize($validated['body_content'] ?? '');
            $documentTemplate->header_text = $sanitize($validated['header_text'] ?? null);
            $documentTemplate->signature_text = $sanitize($validated['signature_text'] ?? null);
            $documentTemplate->variables = $validated['variables'] ?? [];
            $documentTemplate->required_variables = collect($validated['variables'] ?? [])
                ->filter(fn($v) => !empty($v['required']))
                ->pluck('key')
                ->values()
                ->toArray();
            $documentTemplate->page_config = $validated['page_config'] ?? null;
            $documentTemplate->has_qr_code = $request->boolean('has_qr_code');
            $documentTemplate->has_watermark = $request->boolean('has_watermark');
            $documentTemplate->has_signature = $request->boolean('has_signature');
            $documentTemplate->use_designer = true;
            $documentTemplate->template_path = 'documents.templates.universal';
            $documentTemplate->save();

            Log::info('Designer template updated', [
                'template_id' => $documentTemplate->id,
                'user_id' => auth()->id(), 'ip' => request()->ip(), 'ua' => substr(request()->userAgent() ?? '', 0, 200),
            ]);

            return redirect()
                ->route('admin.document-templates.designer', $documentTemplate->id)
                ->with('success', 'Template enregistré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde designer: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualiser le template designer avec des valeurs de test (AJAX HTML)
     */
    public function previewDesigner(Request $request, DocumentTemplate $documentTemplate)
    {
        $data = $request->input('data', []);

        // Compléter avec des valeurs par défaut pour les variables non fournies
        $variables = $documentTemplate->variables ?? [];
        foreach ($variables as $var) {
            $key = is_array($var) ? ($var['key'] ?? null) : null;
            if ($key && !array_key_exists($key, $data)) {
                $data[$key] = is_array($var) ? ($var['default'] ?? '...') : '...';
            }
        }

        try {
            $html = view('documents.templates.universal', [
                'documentTemplate' => $documentTemplate,
                'data' => $data,
                'qr_code_url' => null,
            ])->render();

            return response()->json(['success' => true, 'html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Convertir un template_path (ex: "documents.templates.association.recepisse")
     * en chemin absolu vers le fichier .blade.php
     *
     * SÉCURITÉ : protection contre le path traversal.
     * - Le template_path ne doit contenir que [A-Za-z0-9_.] (pas de / ni ..)
     * - Le chemin résolu DOIT rester dans resource_path('views/documents/')
     */
    private function resolveTemplateAbsolutePath(string $templatePath): string
    {
        // Validation stricte du format
        if (!preg_match('/^[A-Za-z0-9_.]+$/', $templatePath)) {
            abort(400, 'Chemin de template invalide (caractères interdits).');
        }

        // Interdire les séquences suspectes
        if (strpos($templatePath, '..') !== false || strpos($templatePath, '//') !== false) {
            abort(400, 'Chemin de template invalide (séquences interdites).');
        }

        $relative = str_replace('.', '/', $templatePath) . '.blade.php';
        $absolute = resource_path('views/' . $relative);

        // Vérifier que le chemin final est bien dans le dossier documents autorisé
        $allowedBase = realpath(resource_path('views/documents'));
        $resolvedBase = dirname($absolute);
        // realpath peut renvoyer false si le dossier n'existe pas encore
        $resolvedReal = $resolvedBase && is_dir($resolvedBase) ? realpath($resolvedBase) : $resolvedBase;

        if ($allowedBase === false || $resolvedReal === false ||
            strpos($resolvedReal, $allowedBase) !== 0) {
            abort(403, 'Accès au chemin non autorisé.');
        }

        return $absolute;
    }

    /**
     * Afficher l'éditeur de code source du template
     */
    public function editSource(DocumentTemplate $documentTemplate)
    {
        $absolutePath = $this->resolveTemplateAbsolutePath($documentTemplate->template_path);
        $exists = File::exists($absolutePath);
        $content = $exists ? File::get($absolutePath) : '';

        return view('admin.document-templates.edit-source', compact(
            'documentTemplate',
            'content',
            'absolutePath',
            'exists'
        ));
    }

    /**
     * Sauvegarder le code source modifié du template
     */
    public function updateSource(Request $request, DocumentTemplate $documentTemplate)
    {
        // SÉCURITÉ : seuls les super-admins peuvent éditer le code source (privilège élevé)
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent modifier le code source des templates.');
        }

        $request->validate([
            'content' => 'required|string|max:500000',
        ], [
            'content.required' => 'Le contenu du template est obligatoire.',
            'content.max' => 'Le contenu du template ne peut pas dépasser 500 Ko.',
        ]);

        $absolutePath = $this->resolveTemplateAbsolutePath($documentTemplate->template_path);
        $directory = dirname($absolutePath);

        try {
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // Sauvegarde de l'ancienne version (backup)
            if (File::exists($absolutePath)) {
                $backupPath = $absolutePath . '.bak.' . date('YmdHis');
                File::copy($absolutePath, $backupPath);
            }

            File::put($absolutePath, $request->input('content'));

            // Invalider le cache des vues Blade compilées
            if (function_exists('view') && method_exists(app('view'), 'flushFinderCache')) {
                app('view')->flushFinderCache();
            }

            Log::info('Template source updated', [
                'template_id' => $documentTemplate->id,
                'path' => $absolutePath,
                'user_id' => auth()->id(), 'ip' => request()->ip(), 'ua' => substr(request()->userAgent() ?? '', 0, 200),
            ]);

            return redirect()
                ->route('admin.document-templates.edit-source', $documentTemplate->id)
                ->with('success', 'Code source du template mis à jour avec succès. Une sauvegarde de l\'ancienne version a été créée.');

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde template source: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer un template (création d'un nouveau template basé sur un existant)
     */
    public function duplicate(Request $request, DocumentTemplate $documentTemplate)
    {
        $request->validate([
            'code' => 'required|string|max:100|regex:/^[A-Z0-9_]+$/|unique:document_templates,code',
            'nom' => 'required|string|max:255',
            'copy_file' => 'nullable|boolean',
            'new_template_path' => ['nullable', 'string', 'max:500', 'regex:/^[A-Za-z0-9_.]+$/'],
        ], [
            'code.unique' => 'Ce code est déjà utilisé.',
            'code.regex' => 'Le code ne doit contenir que des majuscules, chiffres et underscores.',
            'new_template_path.regex' => 'Le chemin de template ne peut contenir que lettres, chiffres, tiret bas et points.',
        ]);

        // SÉCURITÉ : réservé aux administrateurs (privilège élevé)
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            abort(403, 'Seuls les administrateurs peuvent dupliquer un template.');
        }

        DB::beginTransaction();
        try {
            $copy = $documentTemplate->replicate();
            $copy->code = $request->input('code');
            $copy->nom = $request->input('nom');
            $copy->is_active = false; // Désactivé par défaut pour révision

            // Dupliquer le fichier source si demandé
            $copyFile = $request->boolean('copy_file', true);
            $newTemplatePath = $request->input('new_template_path');

            if ($copyFile) {
                if (!$newTemplatePath) {
                    // Générer un chemin basé sur le code (en minuscules)
                    $originalParts = explode('.', $documentTemplate->template_path);
                    $fileName = Str::slug($request->input('code'), '_');
                    $originalParts[count($originalParts) - 1] = $fileName;
                    $newTemplatePath = implode('.', $originalParts);
                }

                $sourcePath = $this->resolveTemplateAbsolutePath($documentTemplate->template_path);
                $destPath = $this->resolveTemplateAbsolutePath($newTemplatePath);

                if (File::exists($sourcePath)) {
                    $destDir = dirname($destPath);
                    if (!File::isDirectory($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }
                    File::copy($sourcePath, $destPath);
                }

                $copy->template_path = $newTemplatePath;
            }

            $copy->save();

            DB::commit();

            Log::info('Template duplicated', [
                'original_id' => $documentTemplate->id,
                'new_id' => $copy->id,
                'user_id' => auth()->id(), 'ip' => request()->ip(), 'ua' => substr(request()->userAgent() ?? '', 0, 200),
            ]);

            return redirect()
                ->route('admin.document-templates.edit', $copy->id)
                ->with('success', 'Template dupliqué avec succès. Vous pouvez maintenant le modifier.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur duplication template: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualiser un template
     */
    public function preview(DocumentTemplate $documentTemplate)
    {
        $previewContent = null;
        $testData = $this->templateService->generateTestData($documentTemplate);

        try {
            // Essayer de charger le template
            if (View::exists($documentTemplate->template_path)) {
                $previewContent = view($documentTemplate->template_path, $testData)->render();
            }
        } catch (\Exception $e) {
            // Le template n'existe pas ou contient des erreurs
            \Log::error('Erreur prévisualisation template : ' . $e->getMessage());
        }

        return view('admin.document-templates.preview', compact(
            'documentTemplate',
            'previewContent',
            'testData'
        ));
    }

    /**
     * Générer le PDF de prévisualisation
     */
    public function previewPdf(DocumentTemplate $documentTemplate)
    {
        try {
            $testData = $this->templateService->generateTestData($documentTemplate);
            $pdf = $this->templateService->generatePreviewPdf($documentTemplate, $testData);

            // ✅ Utiliser download() au lieu de response()
            $filename = 'preview_' . $documentTemplate->code . '_' . date('YmdHis') . '.pdf';
            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur génération PDF preview: ' . $e->getMessage());

            // ✅ Retourner JSON en cas d'erreur
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du PDF : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX : Charger les workflow steps selon organisation/opération
     */
    public function getWorkflowSteps(Request $request)
    {
        try {
            $organisationTypeId = $request->get('organisation_type_id');
            $operationTypeId = $request->get('operation_type_id');

            if (!$organisationTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type d\'organisation requis',
                    'steps' => []
                ], 400);
            }

            $query = WorkflowStep::where('organisation_type_id', $organisationTypeId)
                ->where('is_active', 1);

            if ($operationTypeId) {
                $query->where('operation_type_id', $operationTypeId);
            }

            $steps = $query->orderBy('numero_passage')
                ->get(['id', 'libelle', 'numero_passage']);

            return response()->json([
                'success' => true,
                'steps' => $steps
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur getWorkflowSteps: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des étapes',
                'steps' => []
            ], 500);
        }
    }


    /**
     * Obtenir la liste des types de documents disponibles
     * 
     * @return array
     */
    private function getTypesDocument(): array
    {
        return [
            'accuse_reception' => 'Accusé de réception',
            'recepisse_provisoire' => 'Récépissé provisoire',
            'recepisse_definitif' => 'Récépissé définitif',
            'certificat_enregistrement' => 'Certificat d\'enregistrement',
            'attestation' => 'Attestation',
            'notification_rejet' => 'Notification de rejet',
            'autre' => 'Autre document',
        ];
    }
}