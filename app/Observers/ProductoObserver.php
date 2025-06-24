<?php

namespace App\Observers;

use App\Models\Producto;

class ProductoObserver
{
    // Se dispara cuando se elimina el registro un producto.
    public function deleting(Producto $producto)
    {
        // Elimina las relaciones entre el producto y las bodegas.
        $producto->bodegas()->detach();
    }
}