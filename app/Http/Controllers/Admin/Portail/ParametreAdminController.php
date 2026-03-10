<?php

namespace App\Http\Controllers\Admin\Portail;

use App\Http\Controllers\Controller;
use App\Models\PortailParametre;
use Illuminate\Http\Request;

class ParametreAdminController extends Controller
{
    public function index()
    {
        $parametres = PortailParametre::orderBy('groupe')->orderBy('cle')->get()->groupBy('groupe');
        return view('admin.portail.parametres.index', compact('parametres'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'parametres'           => 'required|array',
            'parametres.*.valeur'  => 'nullable|string|max:65535',
        ]);

        // Whitelist : seules les clés existantes en BDD peuvent être modifiées
        $clesAutorisees = PortailParametre::pluck('cle')->toArray();

        foreach ($request->parametres as $cle => $row) {
            if (!in_array($cle, $clesAutorisees, true)) {
                continue; // Ignorer silencieusement toute clé inconnue
            }
            PortailParametre::where('cle', $cle)
                ->update(['valeur' => $row['valeur'] ?? null]);
        }

        return redirect()->route('admin.portail.parametres.index')
            ->with('success', 'Paramètres enregistrés avec succès.');
    }

    public function create()
    {
        return view('admin.portail.parametres.form', ['parametre' => new PortailParametre()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cle'         => ['required', 'string', 'max:100', 'unique:portail_parametres,cle', 'regex:/^[a-z0-9_]+$/'],
            'valeur'      => 'nullable|string|max:65535',
            'type'        => 'required|in:text,html,json,image,url,email,phone',
            'groupe'      => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'description' => 'nullable|string|max:255',
        ]);

        PortailParametre::create($data);

        return redirect()->route('admin.portail.parametres.index')
            ->with('success', 'Paramètre créé.');
    }

    public function destroy(PortailParametre $parametre)
    {
        $parametre->delete();
        return redirect()->route('admin.portail.parametres.index')
            ->with('success', 'Paramètre supprimé.');
    }
}
