<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DossierComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'user_id',
        'workflow_step_id',
        'type',
        'commentaire',
        'is_visible_operateur',
        'parent_id',
        'fichiers_joints',
    ];

    protected $casts = [
        'is_visible_operateur' => 'boolean',
        'fichiers_joints' => 'array',
    ];

    /**
     * Le dossier associé
     */
    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * L'utilisateur auteur du commentaire
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'étape du workflow associée
     */
    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    /**
     * Commentaire parent (pour les réponses)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DossierComment::class, 'parent_id');
    }

    /**
     * Réponses à ce commentaire
     */
    public function replies(): HasMany
    {
        return $this->hasMany(DossierComment::class, 'parent_id');
    }

    /**
     * Scopes
     */
    public function scopeInternes($query)
    {
        return $query->where('type', 'interne');
    }

    public function scopeOperateur($query)
    {
        return $query->where('type', 'operateur');
    }

    public function scopeSysteme($query)
    {
        return $query->where('type', 'systeme');
    }

    public function scopeVisiblesOperateur($query)
    {
        return $query->where('is_visible_operateur', true);
    }
}
