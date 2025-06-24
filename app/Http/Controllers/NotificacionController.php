<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class NotificacionController extends Controller
{
    /**
     * Lista las notificaciones del usuario autenticado.
     */
    public function listar()
    {
        // Obtiene las notificaciones del usuario autenticado.
        $notificaciones = Notificacion::selectRaw('notificaciones.*, productos.id AS producto_id, CONCAT(empresas.nombre, " ", marcas.nombre) AS producto_marca, productos.modelo AS producto_modelo, bodegas.id AS bodega_id, bodegas.nombre AS bodega_nombre')
                                      ->join('bodega_producto', 'bodega_producto.id', '=', 'notificaciones.bodega_producto_id')
                                      ->join('bodegas', 'bodegas.id', '=', 'bodega_producto.bodega_id')
                                      ->join('productos', 'productos.id', '=', 'bodega_producto.producto_id')
                                      ->join('marcas', 'marcas.id', '=', 'productos.marca_id')
                                      ->join('fabricantes', 'fabricantes.id', '=', 'marcas.fabricante_id')
                                      ->join('empresas', 'empresas.id', '=', 'fabricantes.empresa_id')
                                      ->where('notificaciones.usuario_id', '=', auth()->user()->id)
                                      ->get();
        
        // Retorna el resultado de la consulta.
        return response($notificaciones, 200);
    }
    
    
    /**
     * Obtiene el número notificaciones no leídas del usuario autenticado.
     */
    public function chequearNuevos()
    {
        return response([
            'total' => auth()->user()->notificaciones()->where('leido', 0)->count()
        ], 200);
    }
    
    /**
     * Marca como leídas las notificaciones del usuario autenticado.
     */
    public function marcarLeidos()
    {
        auth()->user()->notificaciones()->where('leido', 0)->update(['leido' => 1]);
    }
    
    /**
     * Elimina una o todas las notificaciones del usuario autenticado.
     */
    public function eliminar(Request $solicitud)
    {
        // Verifica si se solicitó eliminar todas las notificaciones.
        if ($solicitud->route()->getName() === 'eliminar.todas') {
            // Elimina todas las notificaciones del usuario autenticado.
            auth()->user()->notificaciones()->detach();
            
            // Retorna el resultado de la operación.
            return response([
                'message' => 'Notificaciones eliminadas.'
            ], 200);
        } else {
            // Elimina la notificación del usuario autenticado con el ID proporcionado por el cliente.
            auth()->user()->notificaciones()->detach($solicitud->id);
            
            // Retorna las notificaciones del usuario autenticado.
            return response(auth()->user()->notificaciones()->get(), 200);
        }
    }
}