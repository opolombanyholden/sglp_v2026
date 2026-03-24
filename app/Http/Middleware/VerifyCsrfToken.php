<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken extends Middleware
{
    /**
     * V03 — Rapport ANINF : cookie XSRF-TOKEN avec Secure + SameSite=Strict (CVSS 8.2)
     */
    protected function newCookie($request, $config)
    {
        return new Cookie(
            'XSRF-TOKEN',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $this->shouldForceSecure() ? true : $config['secure'],
            false, // httpOnly must be false for XSRF-TOKEN (JS needs to read it)
            false,
            'Strict'
        );
    }

    protected function shouldForceSecure(): bool
    {
        return app()->environment('production')
            || str_starts_with(config('app.url'), 'https');
    }

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
                    'message' => 'CSRF token mismatch. Veuillez rafraîchir la page et réessayer.',
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
