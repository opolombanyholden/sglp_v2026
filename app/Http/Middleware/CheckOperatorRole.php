<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOperatorRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'operator') {
            abort(403, 'Accès non autorisé');
        }

        if (isset($user->statut) && $user->statut !== 'actif') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        return $next($request);
    }
}