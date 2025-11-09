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
 * CORRECTION : Middleware admin au lieu de can:manage-document-templates
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
            $query->where(function($q) use ($search) {
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
        $typesDocument = DocumentTemplate::getTypesDocument();

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
        $organisationTypes = OrganisationType::orderBy('nom')->get();
        $operationTypes = OperationType::orderBy('nom')->get();
        $typesDocument = DocumentTemplate::getTypesDocument();

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
            'generations' => function($query) {
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
        $operationTypes = OperationType::orderBy('nom')->get();
        $workflowSteps = WorkflowStep::orderBy('numero_passage')->get();
        $typesDocument = DocumentTemplate::getTypesDocument();

        return view('admin.document-templates.edit', compact(
            'documentTemplate',
            'organisationTypes',
            'operationTypes',
            'workflowSteps',
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
            return back()->with('error', 
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
     * Prévisualiser un template
     */
    public function preview(DocumentTemplate $documentTemplate)
    {
        try {
            // Charger une organisation exemple pour la prévisualisation
            $organisation = $documentTemplate->organisationType 
                ? $documentTemplate->organisationType->organisations()->first()
                : null;

            if (!$organisation) {
                return back()->with('error', 'Aucune organisation disponible pour la prévisualisation');
            }

            return view($documentTemplate->template_path, [
                'organisation' => $organisation,
                'template' => $documentTemplate,
                'preview' => true
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la prévisualisation : ' . $e->getMessage());
        }
    }

    /**
     * Prévisualiser en PDF
     */
    public function previewPdf(DocumentTemplate $documentTemplate)
    {
        try {
            $organisation = $documentTemplate->organisationType 
                ? $documentTemplate->organisationType->organisations()->first()
                : null;

            if (!$organisation) {
                return back()->with('error', 'Aucune organisation disponible');
            }

            $pdf = $this->templateService->generatePreviewPdf($documentTemplate, $organisation);

            return $pdf->stream('preview-' . $documentTemplate->code . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur génération PDF : ' . $e->getMessage());
        }
    }

    /**
     * AJAX : Charger les workflow steps selon organisation/opération
     */
    public function getWorkflowSteps(Request $request)
    {
        try {
            $query = WorkflowStep::query();

            if ($request->filled('organisation_type_id')) {
                $query->where('organisation_type_id', $request->organisation_type_id);
            }

            if ($request->filled('operation_type_id')) {
                $query->where('operation_type_id', $request->operation_type_id);
            }

            $steps = $query->orderBy('numero_passage')->get();

            return response()->json([
                'success' => true,
                'steps' => $steps
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des étapes'
            ], 500);
        }
    }
}