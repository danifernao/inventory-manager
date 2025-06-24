<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'departamento_id',
        'nombre'
    ];
    
    // Define la relación uno a muchos de Departamento-Municipio.
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    
    // Define la relación uno a muchos de Municipio-Barrios.
    public function barrios()
    {
        return $this->hasMany(Barrio::class);
    }
    
    // Define la relación uno a muchos de Municipio-Bodega.
    public function bodegas()
    {
        return $this->hasMany(Bodega::class);
    }
    
    // Define la relación uno a muchos de Municipio-Proyecto.
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    
    // Define la relación uno a muchos de Municipio-Empresa.
    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }
}
