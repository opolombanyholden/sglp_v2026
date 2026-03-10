<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortailDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'actualites'  => \App\Models\PortailActualite::count(),
            'publiees'    => \App\Models\PortailActualite::where('statut', 'publie')->count(),
            'documents'   => \App\Models\PortailDocument::count(),
            'faqs'        => \App\Models\PortailFaq::count(),
            'guides'      => \App\Models\PortailGuide::count(),
            'evenements'  => \App\Models\PortailEvenement::count(),
            'messages'    => \App\Models\PortailMessage::count(),
            'non_lus'     => \App\Models\PortailMessage::where('statut', 'non_lu')->count(),
        ];

        $derniers_messages = \App\Models\PortailMessage::orderByDesc('created_at')->limit(5)->get();

        return view('admin.portail.dashboard', compact('stats', 'derniers_messages'));
    }
}
