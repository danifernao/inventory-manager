<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodegaProducto extends Model
{
    use HasFactory;
    
    // Puesto Laravel intenta buscar la tabla "bodega_productos" (pluralización en inglés),
    // y dado que las tablas intermedias van en singular (siguiendo sus convenciones de nomenclatura),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'bodega_producto';
    
    // Define la relación muchos a muchos de Bodega-Producto-Notificacion.
    // Se trae consigo las columnas 'leido', 'created_at' y 'updated_at' de la tabla intermedia.
    public function notificaciones()
    {
        return $this->belongsToMany(Usuario::class, 'notificaciones')
                    ->withPivot('id', 'leido')
                    ->withTimestamps();
    }
}