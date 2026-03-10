<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';
    protected $guarded = ['id'];

    protected $casts = [
        'permissions'  => 'array',
        'est_actif'    => 'boolean',
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
        'rate_limit'   => 'integer',
        'total_requests' => 'integer',
    ];

    /** Génère un nouveau token brut (64 chars) et retourne [token_brut, instance] */
    public static function generate(array $data): array
    {
        $raw    = Str::random(48); // token brut transmis au client
        $hashed = hash('sha256', $raw);
        $prefix = substr($raw, 0, 8);

        $instance = static::create(array_merge($data, [
            'token'  => $hashed,
            'prefix' => $prefix,
        ]));

        return [$raw, $instance];
    }

    /** Recherche un token à partir de la valeur brute */
    public static function findByRaw(string $raw): ?self
    {
        $hashed = hash('sha256', $raw);
        return static::where('token', $hashed)
            ->where('est_actif', true)
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function hasPermission(string $scope): bool
    {
        if (empty($this->permissions)) {
            return true; // accès complet si aucune restriction
        }
        return in_array($scope, $this->permissions, true)
            || in_array('*', $this->permissions, true);
    }

    public function recordUsage(string $ip): void
    {
        $this->increment('total_requests');
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ]);
    }
}
