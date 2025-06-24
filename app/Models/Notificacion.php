<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;
    
    // Puesto Laravel intenta buscar la tabla "notificacions" (pluralización en inglés),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'notificaciones';
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'leido' => 'boolean',
        'created_at' => 'date:d/m/Y'
    ];
}
