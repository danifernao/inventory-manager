<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'direccion',
        'fecha_apertura',
        'fecha_clausura'
    ];
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'fecha_apertura' => 'date:d/m/Y',
        'fecha_clausura' => 'date:d/m/Y'
    ];
    
    // Agrega los atributos especificados.
    protected $appends = [
        'esta_activo'
    ];
    
    // Define la relación uno a muchos de Proyecto-Unidades.
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }
    
    // Define la relación uno a muchos de Pais-Proyecto.
    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }
    
    // Define la relación uno a muchos de Departamento-Proyecto.
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    
    // Define la relación uno a muchos de Municipio-Proyecto.
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    // Define la relación uno a muchos de Barrio-Proyecto.
    public function barrio()
    {
        return $this->belongsTo(Barrio::class);
    }
    
    // Establece el valor del atributo "esta_activo".
    protected function getEstaActivoAttribute()
    {
        return is_null($this->fecha_clausura) || strtotime($this->fecha_clausura) > time();
    }
}