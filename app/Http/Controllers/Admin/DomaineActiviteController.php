<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomaineActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DomaineActiviteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des domaines d'activité
     */
    public function index(Request $request)
    {
        try {
            $query = DomaineActivite::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('statut')) {
                $query->where('is_active', $request->statut === 'actif');
            }

            $domaines = $query->orderBy('ordre')->orderBy('nom')->paginate(20);

            $stats = [
                'total'    => DomaineActivite::count(),
                'actifs'   => DomaineActivite::where('is_active', true)->count(),
                'inactifs' => DomaineActivite::where('is_active', false)->count(),
            ];

            return view('admin.referentiels.domaines-activite.index', compact('domaines', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur DomaineActiviteController@index: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des domaines d\'activité.');
        }
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $maxOrdre = DomaineActivite::max('ordre') ?? 0;
        return view('admin.referentiels.domaines-activite.create', compact('maxOrdre'));
    }

    /**
     * Enregistrer un nouveau domaine
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom'         => 'required|string|max:150',
                'code'        => 'nullable|string|max:50|unique:domaines_activite,code',
                'description' => 'nullable|string|max:1000',
                'ordre'       => 'required|integer|min:0',
                'is_active'   => 'boolean',
            ]);

            if (empty($validated['code'])) {
                $validated['code'] = Str::slug($validated['nom'], '_');
            }

            $validated['is_active'] = $request->has('is_active');

            $domaine = DomaineActivite::create($validated);

            Log::info('Domaine d\'activité créé', ['id' => $domaine->id, 'nom' => $domaine->nom]);

            return redirect()->route('admin.referentiels.domaines-activite.index')
                ->with('success', 'Domaine d\'activité "' . $domaine->nom . '" créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erreur création domaine d\'activité: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création.')->withInput();
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit(DomaineActivite $domaineActivite)
    {
        return view('admin.referentiels.domaines-activite.edit', compact('domaineActivite'));
    }

    /**
     * Mettre à jour un domaine
     */
    public function update(Request $request, DomaineActivite $domaineActivite)
    {
        try {
            $validated = $request->validate([
                'nom'         => 'required|string|max:150',
                'code'        => 'nullable|string|max:50|unique:domaines_activite,code,' . $domaineActivite->id,
                'description' => 'nullable|string|max:1000',
                'ordre'       => 'required|integer|min:0',
                'is_active'   => 'boolean',
            ]);

            if (empty($validated['code'])) {
                $validated['code'] = Str::slug($validated['nom'], '_');
            }

            $validated['is_active'] = $request->has('is_active');

            $domaineActivite->update($validated);

            Log::info('Domaine d\'activité modifié', ['id' => $domaineActivite->id, 'nom' => $domaineActivite->nom]);

            return redirect()->route('admin.referentiels.domaines-activite.index')
                ->with('success', 'Domaine d\'activité "' . $domaineActivite->nom . '" modifié avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erreur modification domaine d\'activité: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la modification.')->withInput();
        }
    }

    /**
     * Supprimer un domaine
     */
    public function destroy(DomaineActivite $domaineActivite)
    {
        try {
            $nbOrganisations = $domaineActivite->organisations()->count();

            if ($nbOrganisations > 0) {
                return back()->with('error',
                    'Impossible de supprimer : ce domaine est utilisé par ' . $nbOrganisations . ' organisation(s).'
                );
            }

            $nom = $domaineActivite->nom;
            $domaineActivite->delete();

            Log::info('Domaine d\'activité supprimé', ['nom' => $nom]);

            return redirect()->route('admin.referentiels.domaines-activite.index')
                ->with('success', 'Domaine d\'activité "' . $nom . '" supprimé avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur suppression domaine d\'activité: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(DomaineActivite $domaineActivite)
    {
        try {
            $domaineActivite->update(['is_active' => !$domaineActivite->is_active]);
            $status = $domaineActivite->is_active ? 'activé' : 'désactivé';

            return back()->with('success', 'Domaine "' . $domaineActivite->nom . '" ' . $status . '.');

        } catch (\Exception $e) {
            Log::error('Erreur toggle domaine d\'activité: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du changement de statut.');
        }
    }
}
