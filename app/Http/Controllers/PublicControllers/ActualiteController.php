<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\PortailActualite;
use Illuminate\Http\Request;

class ActualiteController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailActualite::publie();

        if ($request->filled('categorie') && $request->categorie !== 'all') {
            $query->where('categorie', $request->categorie);
        }

        if ($request->filled('search')) {
            $search = substr($request->search, 0, 255);
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%$search%")
                  ->orWhere('extrait', 'like', "%$search%")
                  ->orWhere('categorie', 'like', "%$search%");
            });
        }

        $actualitesPaginated = $query->paginate(6)->withQueryString();
        $total               = $actualitesPaginated->total();
        $totalPages          = $actualitesPaginated->lastPage();
        $page                = $actualitesPaginated->currentPage();
        $perPage             = 6;
        $search              = $request->search ?? '';
        $categorie           = $request->categorie ?? null;

        $categories     = PortailActualite::where('statut', 'publie')->distinct()->pluck('categorie')->filter()->sort()->values()->toArray();
        $categoryCounts = PortailActualite::where('statut', 'publie')
            ->selectRaw('categorie, count(*) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie')
            ->toArray();

        return view('public.actualites.index', compact(
            'actualitesPaginated',
            'categories',
            'categoryCounts',
            'categorie',
            'total',
            'page',
            'perPage',
            'totalPages',
            'search'
        ));
    }

    public function show($slug)
    {
        $actualite = PortailActualite::where('slug', $slug)
            ->where('statut', 'publie')
            ->firstOrFail();

        $actualite->incrementVues();

        $similaires = PortailActualite::publie()
            ->where('categorie', $actualite->categorie)
            ->where('id', '!=', $actualite->id)
            ->limit(3)
            ->get();

        // Map to array format expected by the Blade view (backward compatibility)
        $actualiteArr = [
            'id'        => $actualite->id,
            'slug'      => $actualite->slug,
            'titre'     => $actualite->titre,
            'date'      => $actualite->date_publication ? $actualite->date_publication->format('Y-m-d') : '',
            'extrait'   => $actualite->extrait,
            'contenu'   => $actualite->contenu,
            'image'     => $actualite->image,
            'categorie' => $actualite->categorie,
            'auteur'    => $actualite->auteur,
            'vues'      => $actualite->vues,
            'tags'      => [],
        ];

        $similairesArr = $similaires->map(fn($s) => [
            'id'        => $s->id,
            'slug'      => $s->slug,
            'titre'     => $s->titre,
            'date'      => $s->date_publication ? $s->date_publication->format('Y-m-d') : '',
            'extrait'   => $s->extrait,
            'image'     => $s->image,
            'categorie' => $s->categorie,
        ])->toArray();

        return view('public.actualites.show', ['actualite' => $actualiteArr, 'similaires' => $similairesArr]);
    }
}
