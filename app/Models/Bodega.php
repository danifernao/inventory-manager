<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bodega extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'nombre',
        'direccion',
        'descripcion',
        'cantidad_max'
    ];
    
    // Define la relación muchos a muchos de Bodega-Producto.
    // Se trae consigo las columnas 'id', 'unidades_minimas', 'created_at' y 'updated_at' de la tabla intermedia.
    public function productos()
    {
        return $this->belongsToMany(Producto::class)
                    ->withPivot('id', 'bodega_id', 'producto_id', 'unidades_minimas')
                    ->withTimestamps();
    }
    
    // Define la relación uno a muchos de Bodega-Unidad.
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
    
    // Define la relación uno a muchos de Pais-Bodega.
    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }
    
    // Define la relación uno a muchos de Departamento-Bodega.
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    
    // Define la relación uno a muchos de Municipio-Bodega.
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    // Define la relación uno a muchos de Barrio-Bodega.
    public function barrio()
    {
        return $this->belongsTo(Barrio::class);
    }
}