<?php

namespace App\Observers;

use App\Models\Movimiento;
use App\Models\Unidad;
use App\Traits\NotificacionTrait;

class UnidadObserver
{
    use NotificacionTrait;
    
    // Se dispara cuando se actualiza el registro de un unidad.
    public function updated(Unidad $unidad)
    {
        // Obtiene el ID de la bodega en donde estaba la unidad.
        $bodegaIdVieja = $unidad->getOriginal('bodega_id');
        
        // Obtiene el ID del proyecto en donde estaba la unidad.
        $proyectoIdViejo = $unidad->getOriginal('proyecto_id');
        
        // Verifica si sus atributos "bodega_id" y "proyecto_id" fueron modificados.
        if($unidad->isDirty('bodega_id') || $unidad->isDirty('proyecto_id')) {
            // Inicia una nueva instancia del modelo Movimiento.
            $movimiento = new Movimiento;
            
            // Asocia el movimiento con la unidad.
            $movimiento->unidad_id = $unidad->id;
            
            // Verifica si la unidad no estaba en una bodega.
            if (is_null($bodegaIdVieja)) {
                // Asocia la fuente del movimiento con el proyecto en donde estaba la unidad.
                $movimiento->fuente_id = $proyectoIdViejo;
                
                // Verifica si la unidad no fue movida una bodega.
                if (is_null($unidad->bodega_id)) {
                    // Se determina que el movimiento se dio entre un proyecto y otro proyecto.
                    $movimiento->tipo = 'PROYECTO_PROYECTO';
                    
                    // Asocia el destino del movimiento con el proyecto en donde está actualmente la unidad.
                    $movimiento->destino_id = $unidad->proyecto_id;
                } else {
                    // Se determina que el movimiento se dio entre un proyecto y una bodega.
                    $movimiento->tipo = 'PROYECTO_BODEGA';
                    
                    // Asocia el destino del movimiento con la bodega en donde está actualmente la unidad.
                    $movimiento->destino_id = $unidad->bodega_id;
                }
            } else {
                // Asocia la fuente del movimiento con la bodega en donde estaba la unidad.
                $movimiento->fuente_id = $bodegaIdVieja;
                
                // Verifica si la unidad no fue movida una bodega.
                if (is_null($unidad->bodega_id)) {
                    // Se determina que el movimiento se dio entre una bodega y un proyecto.
                    $movimiento->tipo = 'BODEGA_PROYECTO';
                    
                    // Asocia el destino del movimiento con el proyecto en donde está actualmente la unidad.
                    $movimiento->destino_id = $unidad->proyecto_id;
                } else {
                    // Se determina que el movimiento se dio entre una bodega y otra bodega.
                    $movimiento->tipo = 'BODEGA_BODEGA';
                    
                    // Asocia el destino del movimiento con la bodega en donde está actualmente la unidad.
                    $movimiento->destino_id = $unidad->bodega_id;
                }
            }
            
            // Inserta el registro en la base de datos.
            $movimiento->save();
        }
        
        // Verifica si la unidad estuvo o está en una bodega.
        if (!is_null($bodegaIdVieja) || !is_null($unidad->bodega_id)) {
            // Verifica si la unidad estuvo en una bodega.
            if (!is_null($bodegaIdVieja) && $unidad->bodega_id !== $bodegaIdVieja) {
                // Constata que haya suficientes existencias del producto en la bodega especificada.
                $this->constatarExistencias($unidad->producto_id, $bodegaIdVieja);
            }
            
            // Verifica si la unidad está en una bodega.
            if (!is_null($unidad->bodega_id)) {
                // Constata que haya suficientes existencias del producto en la bodega especificada.
                $this->constatarExistencias($unidad->producto_id, $unidad->bodega_id);
            }
        }
    }
    
    // Se dispara cuando se elimina el registro de una unidad.
    public function deleted(Unidad $unidad) {
        // Elimina los movimientos de la unidad.
        $unidad->movimientos()->delete();
        
        // Constata que haya suficientes existencias del producto en la bodega en donde estaba la unidad.
        $this->constatarExistencias($unidad->producto_id, $unidad->bodega_id);
    }
}