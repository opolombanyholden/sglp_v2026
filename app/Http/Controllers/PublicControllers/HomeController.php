<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\PortailActualite;
use App\Models\PortailParametre;
use App\Models\PortailFaq;
use App\Models\PortailGuide;
use App\Models\PortailEvenement;
use App\Models\PortailMessage;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $params = PortailParametre::getGroupe('stats');
        $stats = [
            'associations' => $params->get('stats_associations', 150),
            'confessions'  => $params->get('stats_confessions', 45),
            'partis'       => $params->get('stats_partis', 12),
            'ong'          => $params->get('stats_ong', 87),
        ];

        $actualites = PortailActualite::publie()->limit(3)->get();

        $services = [
            ['icon' => 'fas fa-file-alt', 'titre' => 'Formalisation',  'description' => 'Créez et soumettez vos dossiers de formalisation directement en ligne, 24h/24 et 7j/7.'],
            ['icon' => 'fas fa-search',   'titre' => 'Mon Dossier',      'description' => 'Suivez l\'état d\'avancement de vos dossiers en temps réel depuis votre espace personnel.'],
            ['icon' => 'fas fa-comments', 'titre' => 'Interactions',    'description' => 'Échangez directement avec l\'administration via notre messagerie sécurisée intégrée.'],
            ['icon' => 'fas fa-download', 'titre' => 'Documents',      'description' => 'Accédez à tous les documents types, guides et ressources nécessaires à vos démarches.'],
        ];

        return view('home', compact('stats', 'actualites', 'services'));
    }

    public function about()
    {
        $parametres = PortailParametre::getGroupe('about');
        return view('public.about', compact('parametres'));
    }

    public function contact()
    {
        $parametres = PortailParametre::getGroupe('contact');
        return view('public.contact', compact('parametres'));
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'nom'     => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'sujet'   => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ]);

        PortailMessage::create([
            'nom'        => $request->nom,
            'email'      => $request->email,
            'sujet'      => $request->sujet,
            'message'    => $request->message,
            'statut'     => 'non_lu',
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
        ]);

        return redirect()->route('contact')
            ->with('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
    }

    public function documents()
    {
        return redirect()->route('documents.index');
    }

    public function faq()
    {
        $allFaqs = PortailFaq::actif()->get();

        $faqs = [];
        foreach ($allFaqs->groupBy('categorie') as $categorie => $questions) {
            $faqs[strtolower($categorie)] = [
                'titre'     => $categorie,
                'questions' => $questions->map(fn($f) => [
                    'question' => $f->question,
                    'reponse'  => $f->reponse,
                ])->toArray(),
            ];
        }

        return view('public.faq', compact('faqs'));
    }

    public function guides()
    {
        $guides = PortailGuide::actif()->get()->map(fn($g) => [
            'id'             => $g->id,
            'titre'          => $g->titre,
            'description'    => $g->description,
            'pages'          => $g->nombre_pages,
            'mise_a_jour'    => $g->updated_at->format('Y-m-d'),
            'categorie'      => $g->categorie,
            'telechargements'=> $g->nombre_telechargements,
            'chemin_fichier' => $g->chemin_fichier,
            'url_externe'    => $g->url_externe,
        ])->toArray();

        return view('public.guides', compact('guides'));
    }

    public function calendrier()
    {
        $dbEvenements = PortailEvenement::actif()->get();

        $evenements = $dbEvenements->map(fn($e) => [
            'titre'       => $e->titre,
            'date'        => $e->date_debut->format('Y-m-d'),
            'type'        => $e->type,
            'description' => $e->description,
            'important'   => $e->est_important,
            'lieu'        => $e->lieu,
        ])->sortBy('date')->values()->toArray();

        return view('public.calendrier', compact('evenements'));
    }
}
