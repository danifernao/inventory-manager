<?php

namespace App\Http\Controllers;

use App\Exports\MovimientosExport;
use App\Models\Bodega;
use App\Models\Unidad;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class MovimientoController extends Controller
{
    /**
     * Lista los movimientos de una unidad.
     */
    public function listar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'unidad_id' => 'required|integer|gt:0',
            'ordenar_por' => 'sometimes|string|in:created_at',
            'ordenar_en' => 'sometimes|string|in:asc,desc'
        ]);
        
        // Define los valores por defecto de los parámetros de la consulta SQL.
        $ordenar_por = $solicitud->ordenar_por ?: 'created_at'; // Nombre de la columna para la cláusula ORDER BY.
        $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
        
        // Obtiene el registro de la unidad con el ID proporcionado por el cliente.
        $unidad = Unidad::find($solicitud->unidad_id);
        
        // Verifica si se encontró la unidad.
        if ($unidad) {
            // Define la variable que contendrá la lista de movimientos.
            $lista = array();
            
            // Obtiene los movimientos de la unidad.
            $movimientos = $unidad->movimientos()->get()->sortBy([ [$ordenar_por, $ordenar_en] ]);
            
            // Recorre la colección de movientos de la unidad.
            foreach($movimientos as $movimiento) {
                // Define la variable temporal que almacenaró los datos con formato del movimiento iterado.
                $registro = (object) [];
                
                // Gaurda el ID, el tipo y la fecha del movimiento iterado.
                // En fuente y destino se guardarán los respectivos registros, ya sea una bodega o un proyecto.
                $registro->id = $movimiento->id;
                $registro->tipo = $movimiento->tipo;
                $registro->fuente = null;
                $registro->destino = null;
                $registro->fecha = date('d/m/Y', strtotime($movimiento->created_at));
                
                // Los valos del atributo "tipo" siguen la sintaxis FUENTE_DESTINO, los cuales
                // se convierten en un arreglo, obteniendo los siguientes elementos:
                // modelos[0] corresponde al nombre del modelo fuente a consultar.
                // modelos[1] corresponde al nombre del modelo destino a consultar.
                $modelos = explode('_', $movimiento->tipo);
                
                // Recorre el arreglo de los modelos involucrados en el movimiento.
                foreach($modelos as $indice => $modelo) {
                    // Si el indice iterado es cero, se trabaja con el ID del modelo fuente;
                    // de lo contrario, con el ID del modelo destino.
                    $id = $indice === 0 ? $movimiento->fuente_id : $movimiento->destino_id;
                    
                    // Define la variable que contendrá la consulta SQL.
                    $consulta = null;
                    
                    // Verifica si el modelo requerido es Bodega.
                    if ($modelo === 'BODEGA') {
                        // Obtiene el registro de la bodega.
                        $consulta = Bodega::find($id);
                    } else {
                        // Obtiene el registro del proyecto.
                        $consulta = Proyecto::find($id);
                    }
                    
                    // Verifica si se está trabajando con la fuente.
                    if ($indice === 0) {
                        // Guarda los datos de la fuente; si el registro fuente se eliminó, guarda su ID.
                        $registro->fuente = $consulta ?: (object) array('id' => $id);
                    } else {
                        // Guarda los datos del destino; si el registro destino se eliminó, guarda su ID.
                        $registro->destino = $consulta ?: (object) array('id' => $id);
                    }
                }
                
                // Agrega el registro a la lista de movimientos.
                array_push($lista, $registro);
            }
            
            // Verifica si se solcitó exportar la consulta realizada.
            if ($solicitud->exportar) {
                // Inicia una nueva instancia de la clase encargada de dicho proceso.
                // Se le proporciona la lista de movimientos.
                $exportar = new MovimientosExport($lista);
                
                // Retorna el archivo generado.
                return Excel::download($exportar, 'movimientos.xlsx');
            } else {
                // Retorna el resultado de la operación, siguiendo el formato que emplea Laravel
                // en sus habituales respuestas.
                return response([
                    'data' => $lista,
                    'links' => []
                ], 200);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
            return response([
                'message' => 'Unidad no encontrada.'
            ], 404);
        }
    }
}