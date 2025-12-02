<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Fonction
 * Représente les fonctions/rôles possibles pour les membres d'organisations
 * 
 * @property int $id
 * @property string $code
 * @property string $nom
 * @property string|null $nom_feminin
 * @property string|null $description
 * @property string $categorie (bureau, commission, membre)
 * @property int $ordre
 * @property bool $is_bureau
 * @property bool $is_obligatoire
 * @property bool $is_unique
 * @property int $nb_max
 * @property string|null $icone
 * @property string|null $couleur
 * @property bool $is_active
 */
class Fonction extends Model
{
    use HasFactory;

    protected $table = 'fonctions';

    protected $fillable = [
        'code',
        'nom',
        'nom_feminin',
        'description',
        'categorie',
        'ordre',
        'is_bureau',
        'is_obligatoire',
        'is_unique',
        'nb_max',
        'icone',
        'couleur',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_bureau' => 'boolean',
        'is_obligatoire' => 'boolean',
        'is_unique' => 'boolean',
        'ordre' => 'integer',
        'nb_max' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_bureau' => false,
        'is_obligatoire' => false,
        'is_unique' => false,
        'nb_max' => 1,
        'ordre' => 0,
    ];

    /**
     * Scope pour les fonctions actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les fonctions du bureau
     */
    public function scopeBureau($query)
    {
        return $query->where('categorie', 'bureau');
    }

    /**
     * Scope pour les fonctions de commission
     */
    public function scopeCommission($query)
    {
        return $query->where('categorie', 'commission');
    }

    /**
     * Scope pour les fonctions membres
     */
    public function scopeMembre($query)
    {
        return $query->where('categorie', 'membre');
    }

    /**
     * Scope ordonné
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('nom');
    }

    /**
     * Scope pour les fonctions obligatoires
     */
    public function scopeObligatoire($query)
    {
        return $query->where('is_obligatoire', true);
    }

    /**
     * Scope pour les fonctions du bureau exécutif
     */
    public function scopeIsBureau($query)
    {
        return $query->where('is_bureau', true);
    }

    /**
     * Obtenir le nom selon le genre
     */
    public function getNomGenre(string $genre = 'M'): string
    {
        if ($genre === 'F' && !empty($this->nom_feminin)) {
            return $this->nom_feminin;
        }
        return $this->nom;
    }

    /**
     * Obtenir l'icône avec fallback
     */
    public function getIconeAttribute($value): string
    {
        return $value ?: 'fa-user';
    }

    /**
     * Obtenir la couleur avec fallback
     */
    public function getCouleurAttribute($value): string
    {
        return $value ?: '#009e3f';
    }

    /**
     * Vérifier si la fonction peut encore être attribuée
     */
    public function peutEtreAttribuee(int $nbActuel = 0): bool
    {
        if ($this->is_unique && $nbActuel >= 1) {
            return false;
        }
        return $nbActuel < $this->nb_max;
    }

    /**
     * Relation avec les fondateurs (si applicable)
     */
    public function fondateurs()
    {
        return \App\Models\Fondateur::where('fonction', $this->nom);
    }

    /**
     * Compter les utilisations
     */
    public function getNbUtilisationsAttribute(): int
    {
        return \App\Models\Fondateur::where('fonction', $this->nom)->count();
    }
}