<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::orderByDesc('created_at')->get();
        return view('admin.api.tokens.index', compact('tokens'));
    }

    public function create()
    {
        return view('admin.api.tokens.form', ['token' => null, 'newToken' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'                  => 'required|string|max:100',
            'organisation_cliente' => 'nullable|string|max:150',
            'permissions'          => 'nullable|array',
            'permissions.*'        => 'string|in:organisations,stats,verify,*',
            'rate_limit'           => 'required|integer|min:10|max:600',
            'expires_at'           => 'nullable|date|after:today',
            'notes'                => 'nullable|string|max:500',
        ]);

        $data['permissions'] = $data['permissions'] ?? ['organisations', 'stats', 'verify'];

        [$raw, $token] = ApiToken::generate($data);

        return redirect()->route('admin.api.tokens.index')
            ->with('new_token', $raw)
            ->with('new_token_id', $token->id)
            ->with('success', 'Token API créé. Copiez-le maintenant — il ne sera plus affiché.');
    }

    public function destroy(ApiToken $token)
    {
        $token->update(['est_actif' => false]);
        return redirect()->route('admin.api.tokens.index')
            ->with('success', "Token « {$token->nom} » révoqué.");
    }

    public function activate(ApiToken $token)
    {
        $token->update(['est_actif' => true]);
        return redirect()->route('admin.api.tokens.index')
            ->with('success', "Token « {$token->nom} » réactivé.");
    }
}
