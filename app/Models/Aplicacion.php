<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aplicacion extends Model
{
    use HasFactory;
    
    // Puesto Laravel intenta buscar la tabla "aplicacions" (pluralización en inglés),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'aplicacion';
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'titulo',
        'descripcion'
    ];
}
