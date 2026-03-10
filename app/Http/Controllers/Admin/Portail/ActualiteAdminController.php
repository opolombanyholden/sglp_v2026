<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailActualite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActualiteAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailActualite::orderByDesc('date_publication');

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $actualites = $query->paginate(15)->withQueryString();
        $categories = PortailActualite::distinct()->pluck('categorie')->filter()->sort()->values();

        return view('admin.portail.actualites.index', compact('actualites', 'categories'));
    }

    public function create()
    {
        return view('admin.portail.actualites.form', ['actualite' => new PortailActualite()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:portail_actualites,slug',
            'extrait'          => 'nullable|string|max:500',
            'contenu'          => 'required|string',
            'categorie'        => 'required|string|max:100',
            'auteur'           => 'nullable|string|max:255',
            'statut'           => 'required|in:brouillon,publie,archive',
            'en_une'           => 'boolean',
            'date_publication' => 'nullable|date',
            'image'            => 'nullable|image|max:2048',
        ]);

        $data['en_une'] = $request->boolean('en_une');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext  = $file->getClientOriginalExtension();
            $data['image'] = $file->storeAs('portail/actualites', Str::uuid() . '.' . $ext, 'public');
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['titre']);
        }

        PortailActualite::create($data);

        return redirect()->route('admin.portail.actualites.index')
            ->with('success', 'Actualité créée avec succès.');
    }

    public function edit(PortailActualite $actualite)
    {
        return view('admin.portail.actualites.form', compact('actualite'));
    }

    public function update(Request $request, PortailActualite $actualite)
    {
        $data = $request->validate([
            'titre'            => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:portail_actualites,slug,' . $actualite->id,
            'extrait'          => 'nullable|string|max:500',
            'contenu'          => 'required|string',
            'categorie'        => 'required|string|max:100',
            'auteur'           => 'nullable|string|max:255',
            'statut'           => 'required|in:brouillon,publie,archive',
            'en_une'           => 'boolean',
            'date_publication' => 'nullable|date',
            'image'            => 'nullable|image|max:2048',
        ]);

        $data['en_une'] = $request->boolean('en_une');

        if ($request->hasFile('image')) {
            if ($actualite->image) {
                Storage::disk('public')->delete($actualite->image);
            }
            $file = $request->file('image');
            $ext  = $file->getClientOriginalExtension();
            $data['image'] = $file->storeAs('portail/actualites', Str::uuid() . '.' . $ext, 'public');
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['titre']);
        }

        $actualite->update($data);

        return redirect()->route('admin.portail.actualites.index')
            ->with('success', 'Actualité mise à jour avec succès.');
    }

    public function destroy(PortailActualite $actualite)
    {
        if ($actualite->image) {
            Storage::disk('public')->delete($actualite->image);
        }
        $actualite->delete();
        return redirect()->route('admin.portail.actualites.index')
            ->with('success', 'Actualité supprimée.');
    }
}
