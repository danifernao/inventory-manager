<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    use HasFactory;
    
    // Puesto Laravel intenta buscar la tabla "unidads" (pluralización en inglés),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'unidades';
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'bodega_id',
        'producto_id',
        'proveedor_id',
        'proyecto_id',
        'numero_serie',
        'valor_adquisicion',
        'fecha_adquisicion',
        'dado_de_baja',
        'motivo_de_baja',
        'fecha_de_baja',
        'dias_en_uso'
    ];
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'fecha_adquisicion' => 'date:d/m/Y',
        'fecha_ingreso_proyecto' => 'date:d/m/Y',
        'dado_de_baja' => 'boolean',
        'fecha_de_baja' => 'date:d/m/Y'
    ];
    
    // Define la relación uno a muchos de Bodega-Unidad.
    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }
    
    // Define la relación uno a muchos de Producto-Unidad.
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    
    // Define la relación uno a muchos de Proveedor-Unidad.
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    
    // Define la relación uno a muchos de Proyecto-Unidad.
    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }
    
    // Define la relación uno a muchos de Movimiento-Unidad.
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class);
    }
}