<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockSensitivePaths
{
    /**
     * Chemins sensibles à bloquer en production.
     * V01 — Rapport ANINF : exposition de /phpmyadmin (CVSS 9.5)
     */
    private array $blockedPaths = [
        'phpmyadmin',
        'phpMyAdmin',
        'pma',
        'adminer',
        'server-status',
        'server-info',
        '.env',
        '.git',
        'telescope',
        'horizon',
        '_debugbar',
        'setup.php',
        'install.php',
        'wp-admin',
        'wp-login',
    ];

    public function handle(Request $request, Closure $next)
    {
        $path = $request->path();

        foreach ($this->blockedPaths as $blocked) {
            if (str_starts_with($path, $blocked) || str_contains($path, '/' . $blocked)) {
                abort(404);
            }
        }

        return $next($request);
    }
}
