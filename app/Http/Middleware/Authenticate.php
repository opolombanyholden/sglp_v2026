<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            Log::warning('Utilisateur non authentifié redirigé vers login', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => session()->getId(),
                'has_session' => session()->isStarted(),
            ]);

            // Rediriger vers le portail admin si l'URL demandée est sous /admin
            if ($request->is('admin/*') || $request->is('admin')) {
                return route('admin.login');
            }

            return route('login');
        }
    }
}
