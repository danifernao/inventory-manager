<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'fabricante_id',
        'nombre'
    ];
    
    // Define la relación uno a muchos de Fabricante-Marca.
    public function fabricante()
    {
        return $this->belongsTo(Fabricante::class);
    }
    
    // Define la relación uno a muchos de Marca-Producto.
    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
