<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailFaq extends Model
{
    use HasFactory;

    protected $table = 'portail_faqs';
    protected $guarded = ['id'];

    protected $casts = [
        'est_actif' => 'boolean',
        'ordre'     => 'integer',
    ];

    public function scopeActif($query)
    {
        return $query->where('est_actif', true)->orderBy('categorie')->orderBy('ordre');
    }
}
