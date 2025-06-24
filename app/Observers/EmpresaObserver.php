<?php

namespace App\Observers;

use App\Models\Empresa;
use App\Traits\UbicacionTrait;

class EmpresaObserver
{
    use UbicacionTrait;
    
    // Se dispara cuando se elimina el registro de una empresa.
    public function deleted(Empresa $empresa)
    {
        // Elimina la ubicación asociada a la empresa eliminada.
        $this->eliminarUbicacion($empresa->pais_id, $empresa->departamento_id, $empresa->municipio_id, $empresa->barrio_id);
    }
}