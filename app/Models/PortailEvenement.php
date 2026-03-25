<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailEvenement extends Model
{
    use HasFactory;

    protected $table = 'portail_evenements';
    protected $fillable = ['titre', 'description', 'type', 'date_debut', 'date_fin', 'lieu', 'url', 'est_important', 'est_actif'];

    protected $casts = [
        'est_actif'    => 'boolean',
        'est_important'=> 'boolean',
        'date_debut'   => 'date',
        'date_fin'     => 'date',
    ];

    public function scopeActif($query)
    {
        return $query->where('est_actif', true)->orderBy('date_debut');
    }

    public function scopeAVenir($query)
    {
        return $query->where('est_actif', true)->where('date_debut', '>=', now()->toDateString())->orderBy('date_debut');
    }
}
