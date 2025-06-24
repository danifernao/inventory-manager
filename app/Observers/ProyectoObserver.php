<?php

namespace App\Observers;

use App\Models\Proyecto;
use App\Traits\UbicacionTrait;

class ProyectoObserver
{
    use UbicacionTrait;
    
    // Se dispara cuando se elimina el registro de un proyecto.
    public function deleted(Proyecto $proyecto)
    {
        // Elimina la ubicación asociada al proyecto eliminado.
        $this->eliminarUbicacion($proyecto->pais_id, $proyecto->departamento_id, $proyecto->municipio_id, $proyecto->barrio_id);
    }
}
