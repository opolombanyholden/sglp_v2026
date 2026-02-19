<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InscriptionLink extends Model
{
    protected $table = 'inscription_links';

    protected $fillable = [
        'organisation_id',
        'token',
        'url_courte',
        'nom_campagne',
        'description',
        'limite_inscriptions',
        'inscriptions_actuelles',
        'date_debut',
        'date_fin',
        'requiert_validation',
        'champs_supplementaires',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'requiert_validation' => 'boolean',
        'is_active' => 'boolean',
        'champs_supplementaires' => 'array',
        'limite_inscriptions' => 'integer',
        'inscriptions_actuelles' => 'integer',
    ];

    // =========================================================================
    // RELATIONS
    // =========================================================================

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, 'inscription_link_id');
    }

    // =========================================================================
    // BUSINESS LOGIC
    // =========================================================================

    /**
     * Vérifier si le lien est valide pour accepter des inscriptions
     */
    public function isValid(): bool
    {
        // Doit être actif
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now();

        // Vérifier date de début
        if ($this->date_debut && $now->lt($this->date_debut)) {
            return false;
        }

        // Vérifier date de fin
        if ($this->date_fin && $now->gt($this->date_fin)) {
            return false;
        }

        // Vérifier limite d'inscriptions
        if ($this->limite_inscriptions !== null && $this->inscriptions_actuelles >= $this->limite_inscriptions) {
            return false;
        }

        // Vérifier que l'organisation est toujours approuvée
        if ($this->organisation && !$this->organisation->isApprouvee()) {
            return false;
        }

        return true;
    }

    /**
     * Incrémenter le compteur d'inscriptions (atomique)
     */
    public function incrementInscriptions(): void
    {
        $this->increment('inscriptions_actuelles');
    }

    /**
     * Désactiver le lien
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Obtenir le nombre d'inscriptions en attente de validation
     */
    public function getPendingCount(): int
    {
        return $this->adherents()
            ->where('statut_inscription', 'en_attente_validation')
            ->count();
    }

    /**
     * Obtenir l'URL publique complète
     */
    public function getPublicUrl(): string
    {
        return route('public.inscription.form', $this->token);
    }

    /**
     * Vérifier si le lien est expiré
     */
    public function isExpired(): bool
    {
        return $this->date_fin && Carbon::now()->gt($this->date_fin);
    }

    /**
     * Vérifier si la limite d'inscriptions est atteinte
     */
    public function isLimitReached(): bool
    {
        return $this->limite_inscriptions !== null
            && $this->inscriptions_actuelles >= $this->limite_inscriptions;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActives($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForOrganisation($query, int $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeValides($query)
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('date_debut')->orWhere('date_debut', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('date_fin')->orWhere('date_fin', '>=', $now);
            });
    }

    // =========================================================================
    // STATIC METHODS
    // =========================================================================

    /**
     * Générer un token unique
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('token', $token)->exists());

        return $token;
    }
}
