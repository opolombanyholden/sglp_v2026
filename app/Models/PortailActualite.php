<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PortailActualite extends Model
{
    use HasFactory;

    protected $table = 'portail_actualites';
    protected $guarded = ['id'];

    protected $casts = [
        'en_une' => 'boolean',
        'vues'   => 'integer',
        'date_publication' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->titre);
            }
            if (empty($model->date_publication)) {
                $model->date_publication = now();
            }
        });
    }

    public function scopePublie($query)
    {
        return $query->where('statut', 'publie')->orderByDesc('date_publication');
    }

    public function scopeEnUne($query)
    {
        return $query->where('en_une', true)->where('statut', 'publie');
    }

    public function incrementVues()
    {
        $this->increment('vues');
    }
}
