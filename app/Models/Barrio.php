<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barrio extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'municipio_id',
        'nombre'
    ];
    
    // Define la relación uno a muchos de Municipio-Barrio.
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    // Define la relación uno a muchos de Barrio-Bodega.
    public function bodegas()
    {
        return $this->hasMany(Bodega::class);
    }
    
    // Define la relación uno a muchos de Barrio-Proyecto.
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    
    // Define la relación uno a muchos de Barrio-Empresa.
    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }
}
