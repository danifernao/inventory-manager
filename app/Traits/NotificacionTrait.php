<?php 

namespace App\Traits;

use App\Models\Producto;
use App\Models\Unidad;
use App\Models\Usuario;

trait NotificacionTrait
{
    /**
     * Verifica si hay existencias suficientes de un producto en una determinada bodega.
     */
    protected function constatarExistencias($producto_id, $bodega_id)
    {
        // Obtiene los datos de la relación entre el producto y la bodega.
        $bodega_producto = Producto::find($producto_id)->bodegas()->find($bodega_id)->pivot;
        
        // Verifica si se encontró el registro de la relación entre el producto y la bodega.
        if ($bodega_producto) {
            // Obtiene el número de unidades del producto en la bodega.
            $total_unidades = Unidad::where('producto_id', '=', $bodega_producto->producto_id)
                                        ->where('bodega_id', $bodega_producto->bodega_id)
                                        ->where('dado_de_baja', 0)
                                        ->count();
            
            
            // Obtiene los registros de todos los usuarios.
            $usuarios = Usuario::all();
            
            // Recorre la colección de usuarios.
            $usuarios->each(function ($usuario) use ($bodega_producto, $total_unidades) {
                // Verifica si el número de unidades es inferior a las requeridas.
                if ($total_unidades < $bodega_producto->unidades_minimas) {
                    // Verifica que no exista notificación alguna para el producto en la bodega.
                    if (!$usuario->notificaciones->contains($bodega_producto->id)) {
                       // Asocia el usuario iterado con una nueva notificación para el producto en la bodega.
                        $usuario->notificaciones()->attach($bodega_producto->id); 
                    }
                } else {
                    // Verifica si existe una notificación para el producto en la bodega.
                    if ($usuario->notificaciones->contains($bodega_producto->id)) {
                        // Elimina la notificación para el producto en la bodega.
                        $usuario->notificaciones()->detach($bodega_producto->id);
                    }
                }
            });
        }
    }
}