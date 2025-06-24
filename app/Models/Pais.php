<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Pais extends Model
{
    use HasFactory, HasRelationships;
    
    // Puesto Laravel intenta buscar la tabla "paiss" (pluralización en inglés),
    // se le especifica el nombre de la tabla a utilizar.
    protected $table = 'paises';
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'nombre'
    ];
    
    // Define la relación uno a muchos de Pais-Departamento.
    public function departamentos()
    {
        return $this->hasMany(Departamento::class);
    }
    
    // Define la relación uno a muchos de Pais-Municipio a través del modelo Departamento.
    public function municipios()
    {
        return $this->hasManyThrough(Municipio::class, Departamento::class);
    }
    
    // Define la relación uno a muchos de Pais-Barrios.
    public function barrios()
    {
        return $this->hasManyDeep(Barrio::class, [Departamento::class, Municipio::class]);
    }
    
    // Define la relación uno a muchos de Pais-Bodega.
    public function bodegas()
    {
        return $this->hasMany(Bodega::class);
    }
    
    // Define la relación uno a muchos de Pais-Proyecto.
    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
    
    // Define la relación uno a muchos de Pais-Empresa.
    public function empresas()
    {
        return $this->hasMany(Empresa::class);
    }
}
