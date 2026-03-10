<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailFaq;
use Illuminate\Http\Request;

class FaqAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailFaq::orderBy('categorie')->orderBy('ordre');

        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $faqs       = $query->paginate(20)->withQueryString();
        $categories = PortailFaq::distinct()->pluck('categorie')->filter()->sort()->values();

        return view('admin.portail.faqs.index', compact('faqs', 'categories'));
    }

    public function create()
    {
        $categories = PortailFaq::distinct()->pluck('categorie')->filter()->sort()->values();
        return view('admin.portail.faqs.form', ['faq' => new PortailFaq(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question'  => 'required|string|max:500',
            'reponse'   => 'required|string',
            'categorie' => 'required|string|max:100',
            'ordre'     => 'nullable|integer|min:0',
            'est_actif' => 'boolean',
        ]);
        $data['est_actif'] = $request->boolean('est_actif');

        PortailFaq::create($data);

        return redirect()->route('admin.portail.faqs.index')
            ->with('success', 'Question FAQ créée avec succès.');
    }

    public function edit(PortailFaq $faq)
    {
        $categories = PortailFaq::distinct()->pluck('categorie')->filter()->sort()->values();
        return view('admin.portail.faqs.form', compact('faq', 'categories'));
    }

    public function update(Request $request, PortailFaq $faq)
    {
        $data = $request->validate([
            'question'  => 'required|string|max:500',
            'reponse'   => 'required|string',
            'categorie' => 'required|string|max:100',
            'ordre'     => 'nullable|integer|min:0',
            'est_actif' => 'boolean',
        ]);
        $data['est_actif'] = $request->boolean('est_actif');

        $faq->update($data);

        return redirect()->route('admin.portail.faqs.index')
            ->with('success', 'Question FAQ mise à jour avec succès.');
    }

    public function destroy(PortailFaq $faq)
    {
        $faq->delete();
        return redirect()->route('admin.portail.faqs.index')
            ->with('success', 'Question FAQ supprimée.');
    }
}
