<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailDocument::orderBy('ordre');

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        if ($request->filled('type_organisation')) {
            $query->where('type_organisation', $request->type_organisation);
        }

        $documents  = $query->paginate(15)->withQueryString();
        $categories = PortailDocument::distinct()->pluck('categorie')->filter()->sort()->values();

        return view('admin.portail.documents.index', compact('documents', 'categories'));
    }

    public function create()
    {
        return view('admin.portail.documents.form', ['document' => new PortailDocument()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:5000',
            'categorie'         => 'required|string|max:100',
            'type_organisation' => 'nullable|string|max:100',
            'format'            => 'nullable|string|max:20',
            'url_externe'       => 'nullable|url|max:500',
            'est_actif'         => 'boolean',
            'ordre'             => 'nullable|integer|min:0',
            'fichier'           => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ]);

        $data['est_actif'] = $request->boolean('est_actif');

        if ($request->hasFile('fichier')) {
            $file = $request->file('fichier');
            $ext  = $file->getClientOriginalExtension();
            $data['chemin_fichier'] = $file->storeAs('portail/documents', Str::uuid() . '.' . $ext, 'public');
            $data['taille']  = $file->getSize();
            $data['format']  = strtoupper($ext);
        }

        PortailDocument::create($data);

        return redirect()->route('admin.portail.documents.index')
            ->with('success', 'Document ajouté avec succès.');
    }

    public function edit(PortailDocument $document)
    {
        return view('admin.portail.documents.form', compact('document'));
    }

    public function update(Request $request, PortailDocument $document)
    {
        $data = $request->validate([
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:5000',
            'categorie'         => 'required|string|max:100',
            'type_organisation' => 'nullable|string|max:100',
            'format'            => 'nullable|string|max:20',
            'url_externe'       => 'nullable|url|max:500',
            'est_actif'         => 'boolean',
            'ordre'             => 'nullable|integer|min:0',
            'fichier'           => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ]);

        $data['est_actif'] = $request->boolean('est_actif');

        if ($request->hasFile('fichier')) {
            if ($document->chemin_fichier) {
                Storage::disk('public')->delete($document->chemin_fichier);
            }
            $file = $request->file('fichier');
            $ext  = $file->getClientOriginalExtension();
            $data['chemin_fichier'] = $file->storeAs('portail/documents', Str::uuid() . '.' . $ext, 'public');
            $data['taille']  = $file->getSize();
            $data['format']  = strtoupper($ext);
        }

        $document->update($data);

        return redirect()->route('admin.portail.documents.index')
            ->with('success', 'Document mis à jour avec succès.');
    }

    public function destroy(PortailDocument $document)
    {
        if ($document->chemin_fichier) {
            Storage::disk('public')->delete($document->chemin_fichier);
        }
        $document->delete();
        return redirect()->route('admin.portail.documents.index')
            ->with('success', 'Document supprimé.');
    }
}
