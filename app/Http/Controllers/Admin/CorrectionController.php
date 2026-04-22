<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\DossierCorrection;
use App\Models\Organisation;
use App\Services\CorrectionService;
use Illuminate\Http\Request;

class CorrectionController extends Controller
{
    protected CorrectionService $correctionService;

    public function __construct(CorrectionService $correctionService)
    {
        $this->correctionService = $correctionService;
    }

    /**
     * Liste des dossiers de correction
     */
    public function index(Request $request)
    {
        $query = Dossier::where('type_operation', Dossier::TYPE_CORRECTION)
            ->with(['organisation', 'parentDossier', 'corrections.correctedByUser']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_dossier', 'like', "%{$search}%")
                  ->orWhereHas('organisation', function ($oq) use ($search) {
                      $oq->where('nom', 'like', "%{$search}%")
                         ->orWhere('sigle', 'like', "%{$search}%");
                  });
            });
        }

        $dossiers = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.corrections.index', compact('dossiers'));
    }

    /**
     * Sélection de l'organisation à corriger
     */
    public function selectOrganisation(Request $request)
    {
        $query = Organisation::whereHas('dossiers', function ($q) {
                $q->whereIn('statut', [Dossier::STATUT_ACCEPTE, 'approuve'])
                  ->where('is_current_version', true);
            })
            ->with(['organisationType', 'dossiers' => function ($q) {
                $q->whereIn('statut', [Dossier::STATUT_ACCEPTE, 'approuve'])
                  ->where('is_current_version', true);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('sigle', 'like', "%{$search}%")
                  ->orWhere('numero_recepisse', 'like', "%{$search}%");
            });
        }

        $organisations = $query->orderBy('nom')->paginate(20);

        return view('admin.corrections.select-organisation', compact('organisations'));
    }

    /**
     * Formulaire de création d'une correction
     */
    public function create(Organisation $organisation)
    {
        $dossier = Dossier::where('organisation_id', $organisation->id)
            ->whereIn('statut', [Dossier::STATUT_ACCEPTE, 'approuve'])
            ->where('is_current_version', true)
            ->firstOrFail();

        if (!$dossier->canBeCorrected()) {
            return redirect()->route('admin.corrections.index')
                ->with('error', 'Ce dossier ne peut pas être corrigé (correction déjà en cours ou statut invalide).');
        }

        $organisation->load([
            'fondateurs',
            'membresBureau',
            'organisationType',
        ]);

        $provinces = \App\Models\Province::where('is_active', true)->orderBy('nom')->get();

        return view('admin.corrections.create', compact('organisation', 'dossier', 'provinces'));
    }

    /**
     * Enregistrer la correction
     */
    public function store(Request $request, Organisation $organisation)
    {
        $request->validate([
            'motif_global' => 'required|string|max:1000',
            'corrections' => 'required|array|min:1',
            'corrections.*.champ' => 'required|string|max:100',
            'corrections.*.categorie' => 'required|in:organisation,adherent,fondateur,membre_bureau,document,autre',
            'corrections.*.nouvelle_valeur' => 'nullable|string',
            'corrections.*.motif' => 'required|string|max:500',
            'corrections.*.entity_id' => 'nullable|integer',
        ]);

        $dossier = Dossier::where('organisation_id', $organisation->id)
            ->whereIn('statut', [Dossier::STATUT_ACCEPTE, 'approuve'])
            ->where('is_current_version', true)
            ->firstOrFail();

        // Enrichir les corrections avec les anciennes valeurs
        $corrections = collect($request->corrections)->map(function ($correction) use ($organisation) {
            $correction['ancienne_valeur'] = $this->getOldValue(
                $organisation,
                $correction['categorie'],
                $correction['champ'],
                $correction['entity_id'] ?? null
            );
            return $correction;
        })->toArray();

        try {
            $correctionDossier = $this->correctionService->initializeCorrection(
                $dossier,
                $corrections,
                $request->motif_global
            );

            // Soumission directe si demandé
            if ($request->action === 'soumettre') {
                $this->correctionService->submitCorrection($correctionDossier);
                return redirect()->route('admin.corrections.index')
                    ->with('success', "Correction {$correctionDossier->numero_dossier} soumise pour validation.");
            }

            return redirect()->route('admin.corrections.index')
                ->with('success', "Correction {$correctionDossier->numero_dossier} enregistrée en brouillon.");

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Erreur lors de la création de la correction : ' . $e->getMessage());
        }
    }

    /**
     * Page de revue/validation d'une correction
     */
    public function review(Dossier $dossier)
    {
        if ($dossier->type_operation !== Dossier::TYPE_CORRECTION) {
            abort(404);
        }

        $dossier->load([
            'organisation.organisationType',
            'corrections.correctedByUser',
            'parentDossier',
            'operations' => function ($q) {
                $q->orderByDesc('created_at');
            },
        ]);

        return view('admin.corrections.review', compact('dossier'));
    }

    /**
     * Approuver une correction
     */
    public function approve(Request $request, Dossier $dossier)
    {
        $request->validate([
            'commentaire' => 'nullable|string|max:1000',
        ]);

        try {
            $this->correctionService->approveCorrection($dossier, $request->commentaire ?? '');

            return redirect()->route('admin.corrections.index')
                ->with('success', "Correction {$dossier->numero_dossier} approuvée. Les documents ont été invalidés et devront être régénérés.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une correction
     */
    public function reject(Request $request, Dossier $dossier)
    {
        $request->validate([
            'motif_rejet' => 'required|string|max:1000',
        ]);

        try {
            $this->correctionService->rejectCorrection($dossier, $request->motif_rejet);

            return redirect()->route('admin.corrections.index')
                ->with('success', "Correction {$dossier->numero_dossier} rejetée.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Récupérer l'ancienne valeur d'un champ
     */
    private function getOldValue(Organisation $organisation, string $categorie, string $champ, ?int $entityId): ?string
    {
        switch ($categorie) {
            case 'organisation':
                return (string) ($organisation->{$champ} ?? '');

            case 'adherent':
                if ($entityId) {
                    $entity = $organisation->adherents()->find($entityId);
                    return $entity ? (string) ($entity->{$champ} ?? '') : null;
                }
                return null;

            case 'fondateur':
                if ($entityId) {
                    $entity = $organisation->fondateurs()->find($entityId);
                    return $entity ? (string) ($entity->{$champ} ?? '') : null;
                }
                return null;

            case 'membre_bureau':
                if ($entityId) {
                    $entity = $organisation->membresBureau()->find($entityId);
                    return $entity ? (string) ($entity->{$champ} ?? '') : null;
                }
                return null;

            default:
                return null;
        }
    }
}
