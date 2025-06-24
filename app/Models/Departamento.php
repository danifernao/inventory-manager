<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'pais_id',
        'nombre'
    ];
    
    // Define la relación uno a muchos de Pais-Departamento.
    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }
    
    // Define la relación uno a muchos de Departamento-Municipio.
    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
    
    // Define la relación uno a muchos de Departamento-Barrio a través del modelo Municipio.
    public function barrios()
    {
        return $this->hasManyThrough(Barrio::class, Municipio::class);
    }
    
    // Define la relación uno a muchos de Departamento-Bodega.
    public function bodegas()
    {
        return $this->hasMany(Bodega::class);
    }
    
    // Define la relación uno a muchos de Departamento-Proyecto.
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    
    // Define la relación uno a muchos de Departamento-Empresa.
    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }
}
