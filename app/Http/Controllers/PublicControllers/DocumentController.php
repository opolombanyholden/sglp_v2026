<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\PortailDocument;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailDocument::actif();

        if ($request->filled('categorie') && $request->categorie !== 'all') {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('type_organisation', $request->type)
                  ->orWhere('type_organisation', 'tous');
            });
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], substr($request->search, 0, 255));
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'populaire':
                $query->orderByDesc('nombre_telechargements');
                break;
            case 'nom':
                $query->orderBy('titre');
                break;
            default: // recent
                $query->orderByDesc('updated_at');
                break;
        }

        $documents  = $query->get();
        $allDocs    = PortailDocument::actif()->get();
        $categories = $allDocs->pluck('categorie')->unique()->filter()->sort()->values()->toArray();
        $types = [
            'tous'        => 'Tous types',
            'association' => 'Associations',
            'ong'         => 'ONG',
            'parti'       => 'Partis politiques',
            'confession'  => 'Confessions religieuses',
        ];
        $stats = [
            'total'          => $allDocs->count(),
            'telechargements'=> $allDocs->sum('nombre_telechargements'),
        ];

        $categorie = $request->categorie ?? null;
        $type      = $request->type ?? null;
        $search    = $request->search ?? '';

        return view('public.documents.index', compact(
            'documents',
            'categories',
            'types',
            'categorie',
            'type',
            'search',
            'sort',
            'stats'
        ));
    }

    public function download($id)
    {
        $document = PortailDocument::actif()->where('id', $id)->firstOrFail();

        $document->incrementTelechargements();

        if (!empty($document->url_externe)) {
            return redirect($document->url_externe);
        }

        if (!empty($document->chemin_fichier)) {
            $path = storage_path('app/public/' . $document->chemin_fichier);
            if (file_exists($path)) {
                return response()->download($path, basename($document->chemin_fichier));
            }
        }

        return redirect()->route('documents.index')
            ->with('info', 'Le fichier pour "' . $document->titre . '" n\'est pas encore disponible.');
    }
}
