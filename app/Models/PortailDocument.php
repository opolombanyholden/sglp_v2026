<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailDocument extends Model
{
    use HasFactory;

    protected $table = 'portail_documents';
    protected $fillable = ['titre', 'description', 'categorie', 'type_organisation', 'format', 'taille', 'chemin_fichier', 'url_externe', 'nombre_telechargements', 'est_actif', 'ordre'];

    protected $casts = [
        'est_actif' => 'boolean',
        'ordre'     => 'integer',
        'taille'    => 'integer',
        'nombre_telechargements' => 'integer',
    ];

    public function scopeActif($query)
    {
        return $query->where('est_actif', true)->orderBy('ordre');
    }

    public function incrementTelechargements()
    {
        $this->increment('nombre_telechargements');
    }
}
