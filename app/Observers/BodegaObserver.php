<?php

namespace App\Observers;

use App\Models\Bodega;
use App\Traits\UbicacionTrait;

class BodegaObserver
{
    use UbicacionTrait;
    
    // Se dispara cuando se elimina el registro de una bodega.
    public function deleted(Bodega $bodega)
    {
        // Elimina la ubicación asociada a la bodega eliminada.
        $this->eliminarUbicacion($bodega->pais_id, $bodega->departamento_id, $bodega->municipio_id, $bodega->barrio_id);
    }
}