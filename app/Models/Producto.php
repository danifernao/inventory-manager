<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'modelo',
        'descripcion',
        'anos_vida_util'
    ];
    
    // Define la relación muchos a muchos de Bodega-Producto.
    public function bodegas()
    {
        return $this->belongsToMany(Bodega::class)
                    ->withPivot('id', 'bodega_id', 'producto_id', 'unidades_minimas')
                    ->withTimestamps();
    }
    
    // Define la relación uno a muchos de Marca-Producto.
    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }
    
    // Define la relación uno a muchos de Producto-Unidad.
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
}