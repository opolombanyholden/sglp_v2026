<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailEvenement;
use Illuminate\Http\Request;

class EvenementAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailEvenement::orderBy('date_debut');

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $evenements = $query->paginate(15)->withQueryString();

        return view('admin.portail.evenements.index', compact('evenements'));
    }

    public function create()
    {
        return view('admin.portail.evenements.form', ['evenement' => new PortailEvenement()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:echeance,formation,maintenance,evenement',
            'date_debut'    => 'required|date',
            'date_fin'      => 'nullable|date|after_or_equal:date_debut',
            'lieu'          => 'nullable|string|max:255',
            'url'           => 'nullable|url|max:500',
            'est_important' => 'boolean',
            'est_actif'     => 'boolean',
        ]);

        $data['est_important'] = $request->boolean('est_important');
        $data['est_actif']     = $request->boolean('est_actif');

        PortailEvenement::create($data);

        return redirect()->route('admin.portail.evenements.index')
            ->with('success', 'Événement créé avec succès.');
    }

    public function edit(PortailEvenement $evenement)
    {
        return view('admin.portail.evenements.form', compact('evenement'));
    }

    public function update(Request $request, PortailEvenement $evenement)
    {
        $data = $request->validate([
            'titre'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'required|in:echeance,formation,maintenance,evenement',
            'date_debut'    => 'required|date',
            'date_fin'      => 'nullable|date|after_or_equal:date_debut',
            'lieu'          => 'nullable|string|max:255',
            'url'           => 'nullable|url|max:500',
            'est_important' => 'boolean',
            'est_actif'     => 'boolean',
        ]);

        $data['est_important'] = $request->boolean('est_important');
        $data['est_actif']     = $request->boolean('est_actif');

        $evenement->update($data);

        return redirect()->route('admin.portail.evenements.index')
            ->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(PortailEvenement $evenement)
    {
        $evenement->delete();
        return redirect()->route('admin.portail.evenements.index')
            ->with('success', 'Événement supprimé.');
    }
}
