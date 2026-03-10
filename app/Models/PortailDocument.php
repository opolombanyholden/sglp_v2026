<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailDocument extends Model
{
    use HasFactory;

    protected $table = 'portail_documents';
    protected $guarded = ['id'];

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
