<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    
    // Puesto Laravel intenta buscar la tabla "proveedors" (pluralización en inglés),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'proveedores';
    
    // Define la relación uno a uno de Empresa-Provedor.
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    // Define la relación uno a muchos de Proveedor-Unidades.
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}