<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'ip',
        'navegador',
        'plataforma',
        'dispositivo'
    ];
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'date:d/m/Y'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        // Especifica el orden por defecto de los resultados de las consultas SQL.
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('last_used_at', 'desc');
        });
    }
}