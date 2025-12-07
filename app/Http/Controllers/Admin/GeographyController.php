<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;

class GeographyController extends Controller
{
    /**
     * Liste des provinces
     * Route: GET /admin/geolocalisation/provinces
     */
    public function provinces(Request $request)
    {
        try {
            $provinces = Province::query()
                ->withCount(['departements', 'organisations', 'adherents'])
                ->when($request->recherche, function ($q) use ($request) {
                    $q->where('nom', 'like', "%{$request->recherche}%")
                        ->orWhere('code', 'like', "%{$request->recherche}%")
                        ->orWhere('chef_lieu', 'like', "%{$request->recherche}%");
                })
                ->when($request->statut, function ($q) use ($request) {
                    $q->where('is_active', $request->statut === 'actif');
                })
                ->orderBy($request->sort ?? 'ordre_affichage')
                ->paginate(15);

            return view('admin.geolocalisation.provinces.index', compact('provinces'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement des provinces: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création
     * Route: GET /admin/geolocalisation/provinces/create
     */
    public function createProvince()
    {
        return view('admin.geolocalisation.provinces.create');
    }

    /**
     * Enregistrer une nouvelle province
     * Route: POST /admin/geolocalisation/provinces
     */
    public function storeProvince(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces',
            'chef_lieu' => 'nullable|string|max:255',
        ]);

        Province::create($request->all());

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province créée avec succès');
    }

    /**
     * Afficher le formulaire d'édition
     * Route: GET /admin/geolocalisation/provinces/{id}/edit
     */
    public function editProvince($id)
    {
        $province = Province::findOrFail($id);
        return view('admin.geolocalisation.provinces.edit', compact('province'));
    }

    /**
     * Mettre à jour une province
     * Route: PUT /admin/geolocalisation/provinces/{id}
     */
    public function updateProvince(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces,code,' . $id,
            'chef_lieu' => 'nullable|string|max:255',
        ]);

        $province->update($request->all());

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province mise à jour avec succès');
    }

    /**
     * Supprimer une province
     * Route: DELETE /admin/geolocalisation/provinces/{id}
     */
    public function deleteProvince($id)
    {
        $province = Province::findOrFail($id);
        $province->delete();

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province supprimée avec succès');
    }

    /**
     * Afficher les détails d'une province
     * Route: GET /admin/geolocalisation/provinces/{id}
     */
    public function showProvince($id)
    {
        $province = Province::with(['departements', 'organisations', 'adherents'])
            ->findOrFail($id);
        return view('admin.geolocalisation.provinces.show', compact('province'));
    }

    /**
     * Activer/Désactiver une province
     * Route: PATCH /admin/geolocalisation/provinces/{id}/toggle-status
     */
    public function toggleStatusProvince($id)
    {
        $province = Province::findOrFail($id);
        $province->update(['is_active' => !$province->is_active]);

        return redirect()
            ->back()
            ->with('success', 'Statut de la province mis à jour');
    }

    /**
     * Actions groupées sur les provinces
     * Route: POST /admin/geolocalisation/provinces/bulk-action
     */
    public function bulkActionProvince(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'provinces' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->provinces as $provinceId) {
            $province = Province::find($provinceId);
            if ($province) {
                switch ($request->action) {
                    case 'activate':
                        $province->update(['is_active' => true]);
                        $count++;
                        break;
                    case 'deactivate':
                        $province->update(['is_active' => false]);
                        $count++;
                        break;
                    case 'delete':
                        $province->delete();
                        $count++;
                        break;
                }
            }
        }

        return redirect()
            ->back()
            ->with('success', "{$count} province(s) traitée(s) avec succès");
    }

    /**
     * Exporter les provinces
     * Route: GET /admin/geolocalisation/provinces/export
     */
    public function exportProvinces(Request $request)
    {
        $provinces = Province::all();

        // Export CSV basique
        $filename = 'provinces_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($provinces) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Chef-lieu', 'Statut']);

            foreach ($provinces as $province) {
                fputcsv($file, [
                    $province->id,
                    $province->nom,
                    $province->code,
                    $province->chef_lieu,
                    $province->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Liste des arrondissements
     */
    public function arrondissements()
    {
        return view('admin.geolocalisation.arrondissements.index', [
            'arrondissements' => []
        ]);
    }

    public function createArrondissement()
    {
        return view('admin.geolocalisation.arrondissements.create');
    }

    /**
     * Liste des cantons
     */
    public function cantons()
    {
        return view('admin.geolocalisation.cantons.index', [
            'cantons' => []
        ]);
    }

    public function createCanton()
    {
        return view('admin.geolocalisation.cantons.create');
    }

    /**
     * Liste des regroupements
     */
    public function regroupements()
    {
        return view('admin.geolocalisation.regroupements.index', [
            'regroupements' => []
        ]);
    }

    public function createRegroupement()
    {
        return view('admin.geolocalisation.regroupements.create');
    }

    /**
     * Liste des localités
     */
    public function localites()
    {
        return view('admin.geolocalisation.localites.index', [
            'localites' => []
        ]);
    }


    public function createLocalite()
    {
        return view('admin.geolocalisation.localites.create');
    }

    // ==========================================
    // MODULE DÉPARTEMENTS
    // ==========================================

    public function departements(Request $request)
    {
        try {
            $departements = \App\Models\Departement::query()
                ->with(['province'])
                ->when($request->recherche, function ($q) use ($request) {
                    $q->where('nom', 'like', "%{$request->recherche}%")
                        ->orWhere('code', 'like', "%{$request->recherche}%");
                })
                ->when($request->statut, function ($q) use ($request) {
                    $q->where('is_active', $request->statut === 'actif');
                })
                ->when($request->province_id, function ($q) use ($request) {
                    $q->where('province_id', $request->province_id);
                })
                ->orderBy($request->sort ?? 'ordre_affichage')
                ->paginate(15);

            $provinces = Province::orderBy('nom')->get();

            return view('admin.geolocalisation.departements.index', compact('departements', 'provinces'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createDepartement()
    {
        $provinces = Province::orderBy('nom')->get();
        return view('admin.geolocalisation.departements.create', compact('provinces'));
    }

    public function storeDepartement(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departements',
            'province_id' => 'required|exists:provinces,id',
        ]);

        \App\Models\Departement::create($request->all());
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département créé avec succès');
    }

    public function showDepartement($id)
    {
        $departement = \App\Models\Departement::with(['province'])->findOrFail($id);
        return view('admin.geolocalisation.departements.show', compact('departement'));
    }

    public function editDepartement($id)
    {
        $departement = \App\Models\Departement::findOrFail($id);
        $provinces = Province::orderBy('nom')->get();
        return view('admin.geolocalisation.departements.edit', compact('departement', 'provinces'));
    }

    public function updateDepartement(Request $request, $id)
    {
        $departement = \App\Models\Departement::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departements,code,' . $id,
            'province_id' => 'required|exists:provinces,id',
        ]);
        $departement->update($request->all());
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département mis à jour');
    }

    public function deleteDepartement($id)
    {
        \App\Models\Departement::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département supprimé');
    }

    public function toggleStatusDepartement($id)
    {
        $dept = \App\Models\Departement::findOrFail($id);
        $dept->update(['is_active' => !$dept->is_active]);
        return back()->with('success', 'Statut mis à jour');
    }

    public function bulkActionDepartement(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'departements' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->departements as $id) {
            $dept = \App\Models\Departement::find($id);
            if ($dept) {
                if ($request->action == 'delete')
                    $dept->delete();
                else
                    $dept->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} département(s) traité(s)");
    }

    public function exportDepartements()
    {
        $departements = \App\Models\Departement::with('province')->get();
        $filename = 'departements_' . date('Y-m-d') . '.csv';

        $callback = function () use ($departements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Province', 'Statut']);
            foreach ($departements as $d) {
                fputcsv($file, [$d->id, $d->nom, $d->code, $d->province->nom ?? '', $d->is_active ? 'Actif' : 'Inactif']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
