<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Fabricante extends Model
{
    use HasFactory, HasRelationships;
    
    // Define la relación uno a uno de Empresa-Fabricante.
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    
    // Define la relación uno a muchos de Fabricante-Marca.
    public function marcas()
    {
        return $this->hasMany(Marca::class);
    }
    
    // Define la relación uno a muchos de Fabricante-Producto a través del modelo Marca.
    public function productos()
    {
        return $this->hasManyThrough(Producto::class, Marca::class);
    }
    
    // Define la relación uno a muchos de Fabricante-Unidad a través del modelo Producto.
    public function unidades()
    {
        return $this->hasManyDeep(Unidad::class, [Marca::class, Producto::class]);
    }
}