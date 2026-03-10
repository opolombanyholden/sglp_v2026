<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailMessage;
use Illuminate\Http\Request;

class MessageAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = PortailMessage::orderByDesc('created_at');

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('sujet', 'like', '%' . $request->search . '%');
            });
        }

        $messages    = $query->paginate(20)->withQueryString();
        $nonLuCount  = PortailMessage::where('statut', 'non_lu')->count();

        return view('admin.portail.messages.index', compact('messages', 'nonLuCount'));
    }

    public function show(PortailMessage $message)
    {
        $message->marquerLu();
        return view('admin.portail.messages.show', compact('message'));
    }

    public function repondre(Request $request, PortailMessage $message)
    {
        $request->validate([
            'reponse' => 'required|string|min:5|max:10000',
        ]);

        $message->update([
            'reponse'      => $request->reponse,
            'date_reponse' => now(),
            'statut'       => 'traite',
        ]);

        return redirect()->route('admin.portail.messages.show', $message)
            ->with('success', 'Réponse enregistrée avec succès.');
    }

    public function updateStatut(Request $request, PortailMessage $message)
    {
        $request->validate(['statut' => 'required|in:non_lu,lu,traite,archive']);
        $message->update(['statut' => $request->statut]);

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(PortailMessage $message)
    {
        $message->delete();
        return redirect()->route('admin.portail.messages.index')
            ->with('success', 'Message supprimé.');
    }
}
