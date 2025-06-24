<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\Producto;
use App\Traits\UbicacionTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BodegaController extends Controller
{
    use UbicacionTrait;
    
    /**
     * Registra una bodega.
     * Retorna la bodega recién registrada.
     */
    public function registrar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'nombre' => 'required|string|max:255|unique:bodegas,nombre',
            'pais' => 'nullable|required_with:departamento|string|max:255',
            'departamento' => 'nullable|string|required_with:municipio|max:255',
            'municipio' => 'nullable|string|required_with:barrio|string|max:255',
            'barrio' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:500'
        ]);
        
        // Inicia una nueva instancia del modelo Bodega.
        $bodega = new Bodega;
        
        // Rellena los atributos de la instancia recién creada con los campos proporcionados.
        $bodega->fill($solicitud->all());
        
        // Inserta el registro en la base de datos.
        $bodega->save();
        
        // Establece la ubicación de la bodega recién registrada.
        $this->establecerUbicacion($bodega, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
        
        // Actualiza la instancia del modelo con el registro de la base de datos.
        $bodega->refresh();
        
        // Obtiene los registros de todos los productos.
        $productos = Producto::all();
        
        // Recorre la colección de productos.
        $productos->each(function ($producto) use ($bodega) {
            // Relaciona los productos con la bodega recién creada.
            $producto->bodegas()->attach($bodega->id, array('unidades_minimas' => 0));
        });
        
        // Retorna la bodega recién registrada.
        return response($bodega, 201);
    }
    
    /**
     * Retorna todas las bodegas registradas.
     */
    public function listar(Request $solicitud)
    {
        // Obtiene los registros de todas las bodegas.
        $bodegas = Bodega::all();
        // Retorna las bodegas.
        return response($bodegas, 200);
    }
    
    
    /**
     * Obtiene el registro de una bodega.
     */
    public function ver(Request $solicitud)
    {
        // Obtiene el registro de la bodega con el ID proporcionado por el cliente.
        $bodega = Bodega::where('id', $solicitud->id)
                  ->withCount(['unidades' => function ($unidades) {
                      $unidades->where('dado_de_baja', 0);
                  }])
                  ->with(['pais', 'departamento', 'municipio', 'barrio'])
                  ->first();
        
        // Verifica si se encontró la bodega.
        if ($bodega) {
            // Retorna la bodega.
            return response($bodega, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
            return response([
                'message' => 'Bodega no encontrada.'
            ], 404);
        }
    }
    
    
    /**
     * Edita el registro de una bodega.
     * Retorna el registro actualizado.
     */    
    public function editar(Request $solicitud)
    {
        // Obtiene el registro de la bodega con el ID proporcionado por el ciente.
        $bodega = Bodega::find($solicitud->id);
        
        // Verifica si se encontró el registro de la bodega.
        if ($bodega) {
            // Valida los campos proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'nombre' => [ 'required', 'string', 'max:100', Rule::unique('bodegas', 'nombre')->ignore($bodega->id, 'id') ],
                'pais' => 'nullable|required_with:departamento|string|max:255',
                'departamento' => 'nullable|string|required_with:municipio|max:255',
                'municipio' => 'nullable|string|required_with:barrio|string|max:255',
                'barrio' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string|max:500'
            ]);
            
            // Rellena los atributos de la bodega con los campos proporcionados.
            $bodega->fill($solicitud->all());
            
            // Guarda los cambios en la base de datos.
            $bodega->save();
            
            // Establece la ubicación de la bodega.
            $this->establecerUbicacion($bodega, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
            
            // Actualiza la instancia del modelo de la bodega con el registro de la base de datos.
            $bodega->refresh();
            
            // Retorna el registro de la bodega actualizada.
            return response($bodega, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
            return response([
                'message' => 'Bodega no encontrada.'
            ], 404);
        }
    }
    
    
    /**
     * Elimina el registro de una bodega.
     * Procede únicamente cuando la bodega no tiene unidades disponibles asociadas a ella.
     */
    public function eliminar(Request $solicitud)
    {
        // Obtiene el registro de la bodega con el ID proporcionado por el cliente.
        $bodega = Bodega::find($solicitud->id);
        
        // Verifica si se encontró el registro de la bodega.
        if ($bodega) {
            // Verifica si la bodega tiene unidades asociadas a ella.
            if ($bodega->unidades()->exists()) {
                // Genera la respuesta HTTP para este caso.
                return response([
                    'message' => 'No se puede eliminar la bodega mientras existan unidades asociadas a ella.'
                ], 403);
            } else {
                // Elimina la relación de la bodega con los productos.
                $bodega->productos()->detach();
                
                // Elimina el registro de la bodega.
                $bodega->delete();
                
                // Genera la respuesta HTTP del resultado de la operación.
                return response([
                    'message' => 'Bodega eliminada.'
                ], 200);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
            return response([
                'message' => 'Bodega no encontrada.'
            ], 404);
        }
    }
}