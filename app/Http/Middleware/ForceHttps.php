<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    /**
     * V02 — Rapport ANINF : redirection HTTPS→HTTP (CVSS 8.7)
     * Force toutes les requêtes vers HTTPS en production.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldForceHttps() && !$request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }

    private function shouldForceHttps(): bool
    {
        return app()->environment('production')
            || str_starts_with(config('app.url'), 'https');
    }
}
