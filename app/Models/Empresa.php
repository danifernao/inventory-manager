<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'nombre',
        'nit',
        'descripcion',
        'direccion',
        'pagina_web'
    ];
    
    // Define la relación uno a uno de Empresa-Proveedor.
    public function proveedor()
    {
        return $this->hasOne(Proveedor::class);
    }
    
    // Define la relación uno a uno de Empresa-Fabricante.
    public function fabricante()
    {
        return $this->hasOne(Fabricante::class);
    }
    
    // Define la relación uno a muchos de Pais-Empresa.
    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }
    
    // Define la relación uno a muchos de Departamento-Empresa.
    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    
    // Define la relación uno a muchos de Municipio-Empresa.
    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }
    
    // Define la relación uno a muchos de Barrio-Empresa.
    public function barrio()
    {
        return $this->belongsTo(Barrio::class);
    }
}