<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortailParametre extends Model
{
    use HasFactory;

    protected $table = 'portail_parametres';
    protected $guarded = ['id'];

    /**
     * Retrieve a parameter value by key, with optional default.
     */
    public static function get(string $cle, $default = null)
    {
        $param = static::where('cle', $cle)->first();
        return $param ? $param->valeur : $default;
    }

    /**
     * Set (upsert) a parameter value.
     */
    public static function set(string $cle, $valeur, string $type = 'text', string $groupe = 'general')
    {
        return static::updateOrCreate(
            ['cle' => $cle],
            ['valeur' => $valeur, 'type' => $type, 'groupe' => $groupe]
        );
    }

    public static function getGroupe(string $groupe): \Illuminate\Support\Collection
    {
        return static::where('groupe', $groupe)->pluck('valeur', 'cle');
    }
}
