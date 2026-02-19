<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log l'erreur pour debug
            \Log::warning('CSRF Token mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
                'is_ajax' => $request->ajax(),
                'expects_json' => $request->expectsJson(),
            ]);

            // Régénérer le token de session
            $request->session()->regenerateToken();

            // ✅ CORRECTION CRITIQUE : Pour les requêtes AJAX/JSON, retourner un 419 JSON
            // au lieu d'une redirection (qui cause un 405 Method Not Allowed)
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch. Veuillez réessayer.',
                    'new_token' => csrf_token(),
                ], 419);
            }

            // Pour les requêtes classiques : redirection
            if (auth()->check()) {
                return redirect()->back()
                    ->withInput($request->except('_token'))
                    ->with('warning', 'Le formulaire a expiré. Vos données ont été conservées. Veuillez soumettre à nouveau.');
            }

            $referer = $request->headers->get('referer');
            if ($referer) {
                return redirect($referer)
                    ->with('warning', 'Votre session a expiré. Veuillez réessayer.');
            }

            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }
    }
}
