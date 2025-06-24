<?php 

namespace App\Traits;

use App\Models\Barrio;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Pais;

trait UbicacionTrait
{
    /**
     * Establece la ubicación de un registro.
     */
    protected function establecerUbicacion($registro, $pais_nombre, $departamento_nombre, $municipio_nombre, $barrio_nombre)
    {
        // Verifica si se encontró el registro.
        if ($registro) {
            // Guarda los ID de la ubicación del registro.
            $pais_id = $registro->pais_id;
            $departamento_id = $registro->departamento_id;
            $municipio_id = $registro->municipio_id;
            $barrio_id = $registro->barrio_id;
            
            // Verifica si el cliente proporcionó el nombre del país.
            if (!empty($pais_nombre)) {
                // Crea o actualiza el país.
                $pais = Pais::updateOrCreate(
                    ['nombre' => $pais_nombre],
                    ['nombre' => $pais_nombre]
                );
                
                // Asocia el país con el registro.
                $registro->pais_id = $pais->id;
            } else {
                // Retira el país del registro.
                $registro->pais_id = null;
            }
            
            // Verifica si el cliente proporcionó el nombre del departamento.
            if (!is_null($registro->pais_id) && !empty($departamento_nombre)) {
                // Crea o actualiza el departamento.
                $departamento = Departamento::updateOrCreate(
                    ['pais_id' => $registro->pais_id, 'nombre' => $departamento_nombre],
                    ['pais_id' => $registro->pais_id, 'nombre' => $departamento_nombre]
                );
                
                // Asocia el departamento con el registro.
                $registro->departamento_id = $departamento->id;
            } else {
                // Retira el departamento del registro.
                $registro->departamento_id = null;
            }
                
            // Verifica si el cliente proporcionó el nombre del municipio.
            if (!is_null($registro->departamento_id) && !empty($municipio_nombre)) {
                // Crea o actualiza el municipio.
                $municipio = Municipio::updateOrCreate(
                    ['departamento_id' => $registro->departamento_id, 'nombre' => $municipio_nombre],
                    ['departamento_id' => $registro->departamento_id, 'nombre' => $municipio_nombre]
                );
                
                // Asocia el municipio con el registro.
                $registro->municipio_id = $municipio->id;
            } else {
                // Retira el municipio del registro.
                $registro->municipio_id = null;
            }
                    
            // Verifica si el cliente proporcionó el nombre del barrio.
            if (!is_null($registro->municipio_id) && !empty($barrio_nombre)) {
                // Crea o actualiza el barrio.
                $barrio = Barrio::updateOrCreate(
                    ['municipio_id' => $registro->municipio_id, 'nombre' => $barrio_nombre],
                    ['municipio_id' => $registro->municipio_id, 'nombre' => $barrio_nombre]
                );
                
                // Asocia el barrio con el registro.
                $registro->barrio_id = $barrio->id;
            } else {
                // Retira el barrio del registro.
                $registro->barrio_id = null;
            }
            
            // Guarda los cambios hechos al registro.
            $registro->save();
            
            // Elimina los registros de la ubicación huérfanos.
            self::eliminarUbicacion($pais_id, $departamento_id, $municipio_id, $barrio_id);
        }
    }
    
    /**
     * Elimina los registros huérfanos.
     */
    protected function eliminarUbicacion($pais_id, $departamento_id, $municipio_id, $barrio_id)
    {
        // Verifica si se proporcionó el ID del barrio.
        if ($barrio_id) {
            // Obtiene el registro del barrio.
            $barrio = Barrio::find($barrio_id);
            
            // Verifica si el barrio no está asociado con otros registros.
            if ($barrio && !$barrio->bodegas()->exists() && !$barrio->proyectos()->exists() && !$barrio->empresas()->exists()) {
                // Elimina el barrio.
                $barrio->delete();
            }
        }
        
        // Verifica si se proporcionó el ID del municipio.
        if ($municipio_id) {
            // Obtiene el registro del municipio.
            $municipio = Municipio::find($municipio_id);
            
            // Verifica si el municipio no está asociado con otros registros.
            if ($municipio && !$municipio->bodegas()->exists() && !$municipio->proyectos()->exists() && !$municipio->empresas()->exists()) {
                // Elimina el municipio.
                $municipio->delete();
            }
        }
        
        // Verifica si se proporcionó el ID del departamento.
        if ($departamento_id) {
            // Obtiene el registro del departamento.
            $departamento = Departamento::find($departamento_id);
            
            // Verifica si el departamento no está asociado con otros registros.
            if ($departamento && !$departamento->bodegas()->exists() && !$departamento->proyectos()->exists() && !$departamento->empresas()->exists()) {
                // Elimina el departamento.
                $departamento->delete();
            }
        }
        
        // Verifica si se proporcionó el ID del país.
        if ($pais_id) {
            // Obtiene el registro del pais.
            $pais = Pais::find($pais_id);
            
            // Verifica si el pais no está asociado con otros registros.
            if ($pais && !$pais->bodegas()->exists() && !$pais->proyectos()->exists() && !$pais->empresas()->exists()) {
                // Elimina el pais.
                $pais->delete();
            }
        }
    }
}