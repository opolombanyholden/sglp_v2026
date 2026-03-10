<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailGuide extends Model
{
    use HasFactory;

    protected $table = 'portail_guides';
    protected $guarded = ['id'];

    protected $casts = [
        'est_actif'             => 'boolean',
        'ordre'                 => 'integer',
        'nombre_pages'          => 'integer',
        'nombre_telechargements'=> 'integer',
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
