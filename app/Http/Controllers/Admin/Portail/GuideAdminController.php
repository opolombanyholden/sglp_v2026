<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuideAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailGuide::orderBy('ordre');

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $guides     = $query->paginate(15)->withQueryString();
        $categories = PortailGuide::distinct()->pluck('categorie')->filter()->sort()->values();

        return view('admin.portail.guides.index', compact('guides', 'categories'));
    }

    public function create()
    {
        return view('admin.portail.guides.form', ['guide' => new PortailGuide()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'categorie'   => 'required|string|max:100',
            'nombre_pages'=> 'nullable|integer|min:0',
            'url_externe' => 'nullable|url|max:500',
            'est_actif'   => 'boolean',
            'ordre'       => 'nullable|integer|min:0',
            'fichier'     => 'nullable|file|mimes:pdf|max:30720',
        ]);

        $data['est_actif'] = $request->boolean('est_actif');

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $data['chemin_fichier'] = $file->storeAs('portail/guides', Str::uuid() . '.pdf', 'public');
        }

        PortailGuide::create($data);

        return redirect()->route('admin.portail.guides.index')
            ->with('success', 'Guide ajouté avec succès.');
    }

    public function edit(PortailGuide $guide)
    {
        return view('admin.portail.guides.form', compact('guide'));
    }

    public function update(Request $request, PortailGuide $guide)
    {
        $data = $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'categorie'   => 'required|string|max:100',
            'nombre_pages'=> 'nullable|integer|min:0',
            'url_externe' => 'nullable|url|max:500',
            'est_actif'   => 'boolean',
            'ordre'       => 'nullable|integer|min:0',
            'fichier'     => 'nullable|file|mimes:pdf|max:30720',
        ]);

        $data['est_actif'] = $request->boolean('est_actif');

        if ($request->hasFile('fichier')) {
            if ($guide->chemin_fichier) {
                Storage::disk('public')->delete($guide->chemin_fichier);
            }
            $file = $request->file('fichier');
            $data['chemin_fichier'] = $file->storeAs('portail/guides', Str::uuid() . '.pdf', 'public');
        }

        $guide->update($data);

        return redirect()->route('admin.portail.guides.index')
            ->with('success', 'Guide mis à jour avec succès.');
    }

    public function destroy(PortailGuide $guide)
    {
        if ($guide->chemin_fichier) {
            Storage::disk('public')->delete($guide->chemin_fichier);
        }
        $guide->delete();
        return redirect()->route('admin.portail.guides.index')
            ->with('success', 'Guide supprimé.');
    }
}
