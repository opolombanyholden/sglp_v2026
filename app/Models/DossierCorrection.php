<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierCorrection extends Model
{
    protected $table = 'dossier_corrections';

    protected $fillable = [
        'dossier_id',
        'original_dossier_id',
        'champ_corrige',
        'categorie',
        'ancienne_valeur',
        'nouvelle_valeur',
        'motif_correction',
        'entity_id',
        'corrected_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    const CATEGORIE_ORGANISATION = 'organisation';
    const CATEGORIE_ADHERENT = 'adherent';
    const CATEGORIE_FONDATEUR = 'fondateur';
    const CATEGORIE_MEMBRE_BUREAU = 'membre_bureau';
    const CATEGORIE_DOCUMENT = 'document';
    const CATEGORIE_AUTRE = 'autre';

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class, 'dossier_id');
    }

    public function originalDossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class, 'original_dossier_id');
    }

    public function correctedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopeByCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    public function isPending(): bool
    {
        return $this->approved_at === null;
    }

    public function approve(int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function getCategorieLabel(): string
    {
        return match ($this->categorie) {
            self::CATEGORIE_ORGANISATION => 'Informations générales',
            self::CATEGORIE_ADHERENT => 'Adhérent',
            self::CATEGORIE_FONDATEUR => 'Fondateur',
            self::CATEGORIE_MEMBRE_BUREAU => 'Membre du bureau',
            self::CATEGORIE_DOCUMENT => 'Document',
            self::CATEGORIE_AUTRE => 'Autre',
            default => $this->categorie,
        };
    }
}
