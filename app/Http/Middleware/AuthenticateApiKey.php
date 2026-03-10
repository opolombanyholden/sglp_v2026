<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next, string $scope = '*')
    {
        $raw = $this->extractToken($request);

        if (!$raw) {
            return $this->unauthorized('Token d\'API manquant. Utilisez le header Authorization: Bearer <token>.');
        }

        $token = ApiToken::findByRaw($raw);

        if (!$token) {
            return $this->unauthorized('Token d\'API invalide ou révoqué.');
        }

        if ($token->isExpired()) {
            return $this->unauthorized('Token d\'API expiré.');
        }

        if ($scope !== '*' && !$token->hasPermission($scope)) {
            return response()->json([
                'success' => false,
                'error'   => 'FORBIDDEN',
                'message' => "Ce token n'a pas la permission requise : {$scope}.",
            ], 403);
        }

        // Enregistrement de l'usage (fire-and-forget, pas bloquant)
        $token->recordUsage($request->ip());

        // Injecter le token dans la requête pour usage dans les controllers
        $request->attributes->set('api_token', $token);

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        // Fallback : paramètre query (déconseillé mais toléré)
        return $request->query('api_token');
    }

    private function unauthorized(string $message)
    {
        return response()->json([
            'success' => false,
            'error'   => 'UNAUTHORIZED',
            'message' => $message,
        ], 401)->header('WWW-Authenticate', 'Bearer realm="SGLP API"');
    }
}
