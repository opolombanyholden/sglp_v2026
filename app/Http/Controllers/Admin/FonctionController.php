<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fonction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FonctionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des fonctions
     */
    public function index(Request $request)
    {
        try {
            $query = Fonction::query();

            // Filtre par recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtre par catégorie
            if ($request->filled('categorie')) {
                $query->where('categorie', $request->categorie);
            }

            // Filtre par statut
            if ($request->filled('statut')) {
                $query->where('is_active', $request->statut === 'actif');
            }

            // Filtre bureau
            if ($request->filled('bureau')) {
                $query->where('is_bureau', $request->bureau === 'oui');
            }

            $fonctions = $query->orderBy('ordre')->paginate(20);

            // Statistiques
            $stats = [
                'total' => Fonction::count(),
                'actives' => Fonction::where('is_active', true)->count(),
                'bureau' => Fonction::where('is_bureau', true)->count(),
                'obligatoires' => Fonction::where('is_obligatoire', true)->count(),
                'par_categorie' => [
                    'bureau' => Fonction::where('categorie', 'bureau')->count(),
                    'commission' => Fonction::where('categorie', 'commission')->count(),
                    'membre' => Fonction::where('categorie', 'membre')->count(),
                ],
            ];

            $categories = [
                'bureau' => 'Bureau Exécutif',
                'commission' => 'Commission',
                'membre' => 'Membre',
            ];

            return view('admin.fonctions.index', compact('fonctions', 'stats', 'categories'));

        } catch (\Exception $e) {
            Log::error('Erreur FonctionController@index: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des fonctions.');
        }
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $categories = [
            'bureau' => 'Bureau Exécutif',
            'commission' => 'Commission',
            'membre' => 'Membre',
        ];

        $icones = [
            'fa-crown' => 'Couronne',
            'fa-user-tie' => 'Utilisateur cravate',
            'fa-file-alt' => 'Document',
            'fa-file' => 'Fichier',
            'fa-coins' => 'Pièces',
            'fa-wallet' => 'Portefeuille',
            'fa-search-dollar' => 'Recherche financière',
            'fa-balance-scale' => 'Balance',
            'fa-lightbulb' => 'Ampoule',
            'fa-star' => 'Étoile',
            'fa-medal' => 'Médaille',
            'fa-user' => 'Utilisateur',
            'fa-users' => 'Groupe',
            'fa-user-shield' => 'Utilisateur bouclier',
            'fa-gavel' => 'Marteau',
            'fa-handshake' => 'Poignée de main',
        ];

        $couleurs = [
            '#009e3f' => 'Vert Gabon',
            '#00b347' => 'Vert clair',
            '#003f7f' => 'Bleu Gabon',
            '#0056b3' => 'Bleu clair',
            '#ffcd00' => 'Jaune Gabon',
            '#ffc107' => 'Jaune',
            '#dc3545' => 'Rouge',
            '#fd7e14' => 'Orange',
            '#17a2b8' => 'Cyan',
            '#6c757d' => 'Gris',
            '#28a745' => 'Vert',
            '#343a40' => 'Noir',
        ];

        $maxOrdre = Fonction::max('ordre') ?? 0;

        return view('admin.fonctions.create', compact('categories', 'icones', 'couleurs', 'maxOrdre'));
    }

    /**
     * Enregistrer une nouvelle fonction
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:fonctions,code',
                'nom' => 'required|string|max:100',
                'nom_feminin' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:500',
                'categorie' => 'required|in:bureau,commission,membre',
                'ordre' => 'required|integer|min:0',
                'is_bureau' => 'boolean',
                'is_obligatoire' => 'boolean',
                'is_unique' => 'boolean',
                'nb_max' => 'required|integer|min:1|max:999',
                'icone' => 'nullable|string|max:50',
                'couleur' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            // Générer le code si non fourni
            if (empty($validated['code'])) {
                $validated['code'] = Str::slug($validated['nom'], '_');
            }

            $validated['is_bureau'] = $request->has('is_bureau');
            $validated['is_obligatoire'] = $request->has('is_obligatoire');
            $validated['is_unique'] = $request->has('is_unique');
            $validated['is_active'] = $request->has('is_active') || !$request->exists('is_active');

            $fonction = Fonction::create($validated);

            Log::info('Fonction créée', ['id' => $fonction->id, 'nom' => $fonction->nom]);

            return redirect()->route('admin.referentiels.fonctions.index')
                ->with('success', 'Fonction "' . $fonction->nom . '" créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erreur création fonction: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création.')->withInput();
        }
    }

    /**
     * Afficher une fonction
     */
    public function show(Fonction $fonction)
    {
        // Statistiques d'utilisation
        $stats = [
            'nb_fondateurs' => \App\Models\Fondateur::where('fonction', $fonction->nom)->count(),
        ];

        return view('admin.fonctions.show', compact('fonction', 'stats'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Fonction $fonction)
    {
        $categories = [
            'bureau' => 'Bureau Exécutif',
            'commission' => 'Commission',
            'membre' => 'Membre',
        ];

        $icones = [
            'fa-crown' => 'Couronne',
            'fa-user-tie' => 'Utilisateur cravate',
            'fa-file-alt' => 'Document',
            'fa-file' => 'Fichier',
            'fa-coins' => 'Pièces',
            'fa-wallet' => 'Portefeuille',
            'fa-search-dollar' => 'Recherche financière',
            'fa-balance-scale' => 'Balance',
            'fa-lightbulb' => 'Ampoule',
            'fa-star' => 'Étoile',
            'fa-medal' => 'Médaille',
            'fa-user' => 'Utilisateur',
            'fa-users' => 'Groupe',
            'fa-user-shield' => 'Utilisateur bouclier',
            'fa-gavel' => 'Marteau',
            'fa-handshake' => 'Poignée de main',
        ];

        $couleurs = [
            '#009e3f' => 'Vert Gabon',
            '#00b347' => 'Vert clair',
            '#003f7f' => 'Bleu Gabon',
            '#0056b3' => 'Bleu clair',
            '#ffcd00' => 'Jaune Gabon',
            '#ffc107' => 'Jaune',
            '#dc3545' => 'Rouge',
            '#fd7e14' => 'Orange',
            '#17a2b8' => 'Cyan',
            '#6c757d' => 'Gris',
            '#28a745' => 'Vert',
            '#343a40' => 'Noir',
        ];

        return view('admin.fonctions.edit', compact('fonction', 'categories', 'icones', 'couleurs'));
    }

    /**
     * Mettre à jour une fonction
     */
    public function update(Request $request, Fonction $fonction)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:fonctions,code,' . $fonction->id,
                'nom' => 'required|string|max:100',
                'nom_feminin' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:500',
                'categorie' => 'required|in:bureau,commission,membre',
                'ordre' => 'required|integer|min:0',
                'is_bureau' => 'boolean',
                'is_obligatoire' => 'boolean',
                'is_unique' => 'boolean',
                'nb_max' => 'required|integer|min:1|max:999',
                'icone' => 'nullable|string|max:50',
                'couleur' => 'nullable|string|max:20',
                'is_active' => 'boolean',
            ]);

            $validated['is_bureau'] = $request->has('is_bureau');
            $validated['is_obligatoire'] = $request->has('is_obligatoire');
            $validated['is_unique'] = $request->has('is_unique');
            $validated['is_active'] = $request->has('is_active');

            $fonction->update($validated);

            Log::info('Fonction modifiée', ['id' => $fonction->id, 'nom' => $fonction->nom]);

            return redirect()->route('admin.referentiels.fonctions.index')
                ->with('success', 'Fonction "' . $fonction->nom . '" modifiée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erreur modification fonction: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la modification.')->withInput();
        }
    }

    /**
     * Supprimer une fonction
     */
    public function destroy(Fonction $fonction)
    {
        try {
            // Vérifier si utilisée
            $nbUtilisations = \App\Models\Fondateur::where('fonction', $fonction->nom)->count();
            
            if ($nbUtilisations > 0) {
                return back()->with('error', 'Impossible de supprimer: cette fonction est utilisée par ' . $nbUtilisations . ' fondateur(s).');
            }

            $nom = $fonction->nom;
            $fonction->delete();

            Log::info('Fonction supprimée', ['nom' => $nom]);

            return redirect()->route('admin.referentiels.fonctions.index')
                ->with('success', 'Fonction "' . $nom . '" supprimée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur suppression fonction: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(Fonction $fonction)
    {
        try {
            $fonction->update(['is_active' => !$fonction->is_active]);
            
            $status = $fonction->is_active ? 'activée' : 'désactivée';
            
            return back()->with('success', 'Fonction "' . $fonction->nom . '" ' . $status . '.');

        } catch (\Exception $e) {
            Log::error('Erreur toggle fonction: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du changement de statut.');
        }
    }

    /**
     * Réordonner les fonctions (AJAX)
     */
    public function reorder(Request $request)
    {
        try {
            $request->validate([
                'ordres' => 'required|array',
                'ordres.*.id' => 'required|exists:fonctions,id',
                'ordres.*.ordre' => 'required|integer|min:0',
            ]);

            foreach ($request->ordres as $item) {
                Fonction::where('id', $item['id'])->update(['ordre' => $item['ordre']]);
            }

            return response()->json(['success' => true, 'message' => 'Ordre mis à jour.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Liste des fonctions pour select dynamique
     */
    public function apiList(Request $request)
    {
        try {
            $query = Fonction::active()->ordered();

            if ($request->filled('categorie')) {
                $query->where('categorie', $request->categorie);
            }

            if ($request->filled('bureau')) {
                $query->where('is_bureau', true);
            }

            $fonctions = $query->get(['id', 'code', 'nom', 'nom_feminin', 'categorie', 'is_bureau', 'icone', 'couleur']);

            // Retourner les fonctions groupées par catégorie si demandé
            if ($request->filled('grouped') && $request->grouped) {
                $grouped = [
                    'bureau' => $fonctions->where('categorie', 'bureau')->values(),
                    'commission' => $fonctions->where('categorie', 'commission')->values(),
                    'membre' => $fonctions->where('categorie', 'membre')->values(),
                ];

                return response()->json([
                    'success' => true,
                    'data' => $grouped
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $fonctions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur API fonctions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }
}