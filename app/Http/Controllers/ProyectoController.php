<?php

namespace App\Http\Controllers;

use App\Exports\ProyectosExport;
use App\Models\Proyecto;
use App\Traits\UbicacionTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProyectoController extends Controller
{
    use UbicacionTrait;
    
    /**
     * Registra un proyecto.
     * Retorna el proyecto recién registrado.
     */
    public function registrar (Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'nombre' => 'required|string|max:255|unique:proyectos,nombre',
            'codigo' => 'nullable|string|max:255|unique:proyectos,codigo',
            'descripcion' => 'nullable|string|max:500',
            'pais' => 'nullable|required_with:departamento|string|max:255',
            'departamento' => 'nullable|string|required_with:municipio|max:255',
            'municipio' => 'nullable|string|required_with:barrio|string|max:255',
            'barrio' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'fecha_apertura' => 'nullable|date',
            'fecha_clausura' => 'nullable|date'
        ]);
        
        // Inicia una nueva instancia del modelo Proyecto.
        $proyecto = new Proyecto;
        
        // Rellena los atributos de la instancia recién creada con los campos proporcionados por el cliente.
        $proyecto->fill($solicitud->all());
        
        // Inserta el registro en la base de datos.
        $proyecto->save();
        
        // Establece la ubicación del proyecto recién registrado.
        $this->establecerUbicacion($proyecto, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
        
        // Actualiza la instancia Proyecto con el respectivo registro de la base de datos.
        $proyecto->refresh();
        
        // Retorna el proyecto recién registrado.
        return response($proyecto, 201);
    }
    
    
    /**
     * Retorna los proyectos registrados, teniendo en cuenta los parámetros de consulta proporcionados.
     */
    public function listar(Request $solicitud)
    {
        // Verifica si se requiere una lista simple, es decir, sin estar paginada y con solo los nombres e ID de los registros.
        if ($solicitud->lista === 'simple') {
            // Obtiene los nombres y los ID de todos los proyectos.
            $proyectos = Proyecto::select('id', 'nombre')->get();
            
            // Retorna la lista de proyectos.
            return response($proyectos, 200);
        } else {
            // Valida los parámetros proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'buscar' => 'sometimes|string',
                'ordenar_por' => 'sometimes|string|in:nombre,codigo,ubicacion,fecha_apertura,fecha_clausura,unidades_count,created_at',
                'ordenar_en' => 'sometimes|string|in:asc,desc',
                'por_pagina' => 'sometimes|numeric|digits_between:1,3|between:1,300'
            ]);
            
            // Define los valores por defecto de los parámetros de la consulta SQL.
            $ordenar_por = $solicitud->ordenar_por ?: 'nombre'; // Nombre de la columna para la cláusula ORDER BY.
            $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
            $por_pagina = $solicitud->por_pagina ?: 20; // Valor para la cláusula LIMIT.
            
            // Prepara la consulta para obtener los proyectos con unidades disponibles.
            $consulta = Proyecto::selectRaw('proyectos.*, CONCAT_WS(", ", paises.nombre, departamentos.nombre, municipios.nombre) AS ubicacion')
                                ->withCount(['unidades' => function ($unidades) {
                                    $unidades->where('dado_de_baja', 0);
                                }])
                                ->leftJoin('paises', 'paises.id', '=', 'proyectos.pais_id')
                                ->leftJoin('departamentos', 'departamentos.id', '=', 'proyectos.departamento_id')
                                ->leftJoin('municipios', 'municipios.id', '=', 'proyectos.municipio_id');
            
            // Verifica si se solcitó exportar la consulta realizada.
            if ($solicitud->exportar) {
                // Inicia una nueva instancia de la clase encargada de dicho proceso.
                // Se le proporciona el resultado de la consulta.
                $exportar = new ProyectosExport($consulta->get());
                
                // Retorna el archivo generado.
                return Excel::download($exportar, 'proyectos.xlsx');
            } else {
                // Verifica si se solicitó la búsqueda de una cadena de texto.
                if ($solicitud->filled('buscar')) {
                    // Convierte la cadena en un arreglo, separando cada término.
                    $terminos = explode(' ', $solicitud->buscar);
                    
                    // Define las columnas en las que se realizará la búsqueda.
                    $columnas = ['nombre', 'codigo', 'ubicacion'];
                    
                    // Recorre el arreglo de los términos.
                    foreach ($terminos as $termino) {
                        // Busca los registros de la consulta que contenga el término iterado.
                        $consulta = $consulta->orWhere(function ($sub_consulta) use ($columnas, $termino) {
                            foreach ($columnas as $columna) {
                                $sub_consulta->orWhere($columna, 'LIKE', '%' . $termino . '%');
                            }
                        });
                    }
                }
                
                // Ordena la consulta de acuerdo con los parámetros definidos y la pagina.
                $consulta = $consulta->orderBy($ordenar_por, $ordenar_en)
                                     ->paginate($por_pagina)->withQueryString()->onEachSide(3);
                
                // Retorna el resultado de la consulta.
                return response($consulta, 200);
            }
        }
    }
    
    
    /**
     * Obtiene el registro de un proyecto.
     */
    public function ver (Request $solicitud)
    {
        // Obtiene el registro del proyecto con el ID proporcionado por el cliente.
        $proyecto = Proyecto::where('id', $solicitud->id)->with(['pais', 'departamento', 'municipio', 'barrio'])->first();
        
        // Verifica si se encontró el proyecto.
        if ($proyecto) {
            // Retorna el registro del proyecto.
            return response($proyecto, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
            return response([
                'message' => 'Proyecto no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Edita el registro de un proyecto.
     * Retorna el registro actualizado.
     */    
    public function editar(Request $solicitud)
    {
        // Obtiene el registro del proyecto con el ID proporcionado por el cliente.
        $proyecto = Proyecto::find($solicitud->id);
        
        // Verifica si se encontró el proyecto.
        if ($proyecto) {
            // Valida los parámetros proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'nombre' => 'required|string|max:255|unique:proyectos,nombre,'.$proyecto->id.',id',
                'codigo' => 'nullable|string|max:255|unique:proyectos,codigo,'.$proyecto->id.',id',
                'descripcion' => 'nullable|string|max:500',
                'pais' => 'nullable|required_with:departamento|string|max:255',
                'departamento' => 'nullable|string|required_with:municipio|max:255',
                'municipio' => 'nullable|string|required_with:barrio|string|max:255',
                'barrio' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'fecha_apertura' => 'nullable|date',
                'fecha_clausura' => 'nullable|date'
            ]);
            
            // Rellena los atributos de la instancia recién creada con los campos proporcionados por el cliente.
            $proyecto->fill($solicitud->all());
            
            // Inserta el registro en la base de datos.
            $proyecto->save();
            
            // Establece la ubicación del proyecto.
            $this->establecerUbicacion($proyecto, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
            
            // Actualiza la instancia Proyecto con el respectivo registro de la base de datos.
            $proyecto->refresh();
            
            // Retorna el registro del proyecto.
            return response($proyecto, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
            return response([
                'message' => 'Proyecto no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Elimina el registro de un proyecto.
     * Procede únicamente cuando el proyecto no tiene unidades asociadas a él.
     */
    public function eliminar (Request $solicitud)
    {
        // Obtiene el registro del proyecto con el ID proporcionado por el cliente.
        $proyecto = Proyecto::find($solicitud->id);
        
        // Verifica si se encontró el proyecto.
        if ($proyecto) {
            // Verifica si el proyecto tiene unidades asociadas a él.
            if ($proyecto->unidades()->exists()) {
                // Genera la respuesta HTTP para este caso.
                return response([
                    'message' => 'No se puede eliminar el proyecto mientras existan unidades asociadas a él.'
                 ], 403);
            } else {
                // Elimina el registro del proyecto.
                $proyecto->delete();
                
                // Genera la respuesta HTTP del resultado de la operación.
                return response([
                    'message' => 'Proyecto eliminado.'
                ], 200);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
            return response([
                'message' => 'Proyecto no encontrado.'
            ], 404);
        }
    }
}