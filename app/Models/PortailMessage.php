<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailMessage extends Model
{
    use HasFactory;

    protected $table = 'portail_messages';
    protected $guarded = ['id'];

    protected $casts = [
        'date_reponse' => 'datetime',
    ];

    public function scopeNonLu($query)
    {
        return $query->where('statut', 'non_lu');
    }

    public function marquerLu()
    {
        if ($this->statut === 'non_lu') {
            $this->update(['statut' => 'lu']);
        }
    }
}
