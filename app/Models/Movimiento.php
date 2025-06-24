<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'created_at' => 'date:d/m/Y'
    ];
    
    // Define la relación uno a muchos de Unidad-Movimiento.
    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }
}
