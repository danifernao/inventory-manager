<?php

namespace App\Http\Controllers;

use App\Exports\UnidadesExport;
use App\Models\Bodega;
use App\Models\Fabricante;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\Unidad;
use App\Traits\NotificacionTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UnidadController extends Controller
{
    use NotificacionTrait;
    
    /**
     * Registra varias unidades de un producto.
     */
    public function registrar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'required|integer|gt:0',
            'producto_id' => 'required|integer|gt:0',
            'proveedor_id' => 'sometimes|integer|gt:0',
            'nros_serie' => 'required|string',
            'fecha_adquisicion' => 'nullable|date',
            'valor_adquisicion' => 'nullable|numeric'
        ]);
        
        // Obtiene el registro de la bodega.
        $bodega = Bodega::find($solicitud->bodega_id);
        
        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
        if (!$bodega) {
            return response([
                'message' => 'Bodega no encontrada.'
            ], 404);
        }
        
        // Obtiene el registro del producto.
        $producto = Producto::find($solicitud->producto_id);
        
        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del producto.
        if (!$producto) {
            return response([
                'message' => 'Producto no encontrado.'
            ], 404);
        }
        
        // Obtiene el registro del proveedor, en tal caso el cliente haya proporcionado su respectivo ID.
        $proveedor = $solicitud->filled('proveedor_id') ? Proveedor::find($solicitud->proveedor_id) : false;
        
        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
        // El valor será "false" si el cliente no proporcionó ID alguno, de ahí que solo se evalúe cuando es nulo (no encontrado).
        if (is_null($proveedor)) {
            return response([
                'message' => 'Proveedor no encontrado.'
            ], 404);
        }
        
        // Decodifica la cadena JSON "series".
        $series = json_decode($solicitud->nros_serie);
        
        // Verifica si se dispone de un arreglo de números de serie.
        if (is_array($series) && count($series) > 0) {
            // Recorre el arreglo de los números de series.
            foreach($series as $i => $serie) {
                // Verifica si el elemento iterado tiene algo.
                if (!empty($serie)) {
                    // Iserta el registro en la base de datos con los campos proporcionados por el cliente.
                    // En caso de que ya exista ("numero_serie" debe ser único), ignora la inserción.
                    Unidad::insertOrIgnore([
                        'bodega_id' => $solicitud->bodega_id,
                        'producto_id' => $solicitud->producto_id,
                        'proveedor_id' => $solicitud->proveedor_id,
                        'numero_serie' => $serie,
                        'fecha_adquisicion' => $solicitud->fecha_adquisicion,
                        'valor_adquisicion' => $solicitud->valor_adquisicion
                    ]);
                }
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se tenga un arreglo.
            return response([
                'message' => 'El campo número de serie es obligatorio.'
            ], 400);
        }
        
        // Constata que haya suficientes existencias del producto en la bodega correspondiente.
        $this->constatarExistencias($solicitud->producto_id, $solicitud->bodega_id);
        
        // Retorna el resultado del proceso.
        return response([
            'message' => 'Unidades registradas.'
        ], 201);
    }
    
    
    /**
     * Retorna las unidades registradas, teniendo en cuenta los parámetros de consulta proporcionados por el cliente.
     */    
    public function listar(Request $solicitud)
    {
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'sometimes|required_without:proyecto_id|integer|gt:0',
            'producto_id' => 'required|integer|gt:0',
            'proveedor_id' => 'sometimes|integer|gt:0',
            'fabricante_id' => 'sometimes|integer|gt:0',
            'proyecto_id' => 'sometimes|required_without:bodega_id|integer|gt:0',
            'buscar' => 'sometimes|string',
            'ordenar_por' => 'sometimes|string|in:numero_serie,proveedor_nombre,fecha_adquisicion,valor_adquisicion,valor_residual,depreciacion,fecha_de_baja,fecha_ingreso_proyecto,created_at',
            'ordenar_en' => 'sometimes|string|in:asc,desc',
            'por_pagina' => 'sometimes|integer|between:1,300',
            'dado_de_baja' => 'sometimes|boolean'
        ]);
        
        // Define los valores por defecto de los parámetros de la consulta SQL.
        $ordenar_por = $solicitud->ordenar_por ?: 'numero_serie'; // Nombre de la columna para la cláusula ORDER BY.
        $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
        $por_pagina = $solicitud->por_pagina ?: 20; // Valor para la cláusula LIMIT.
        $dado_de_baja = $solicitud->dado_de_baja ?: 0; // Valor para condicionar la consulta de las unidades. Puede ser 1 o 0.
        
        // Define las variables que contendrán los registros de la bodega y del proyecto.
        $bodega = null;
        $proyecto = null;
        
        // Prepara la consulta a realizar.
        // Obtiene todas las columnas de las unidades.
        $consulta = Unidad::select('unidades.*');
        
        // Obtiene el registro del producto.
        $producto = Producto::find($solicitud->producto_id);
        
        // Verifica si se encontró el producto.
        if ($producto) {
            // Condiciona la consulta de unidades, trayéndose solo aquellas que estén asociadas al producto.
            $consulta = $consulta->where('producto_id', $producto->id);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del producto.
            return response([
                'message' => 'Producto no encontrado.'
            ], 404);
        }
        
        // Verifica si el cliente proporcionó el ID de la bodega.
        if ($solicitud->filled('bodega_id')) {
            // Obtiene el registro de la bodega.
            $bodega = Bodega::find($solicitud->bodega_id);
            
            // Verifica si se encontró la bodega.
            if ($bodega) {
                // Condiciona la consulta de unidades, trayéndose solo aquellas que estén asociadas a la bodega.
                $consulta = $consulta->where('bodega_id', $bodega->id);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
                return response([
                    'message' => 'Bodega no encontrada.'
                ], 404);
            }
        } else {
            // Verifica si el cliente proporcionó el ID del proyecto.
            if ($solicitud->filled('proyecto_id')) {
                // Obtiene el registro del proyecto.
                $proyecto = Proyecto::find($solicitud->proyecto_id);
                
                // Verifica si se encontró el proyecto.
                if ($proyecto) {
                    // Condiciona la consulta de unidades, trayéndose solo aquellas que estén asociadas al proyecto.
                    $consulta = $consulta->where('proyecto_id', $proyecto->id);
                } else {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
                    return response([
                        'message' => 'Proyecto no encontrado.'
                    ], 404);
                }
            } else {
                // Genera la respuesta HTTP en caso de que el cliente no haya proporcionado el ID de la bodega o del proyecto.
                return response([
                    'message' => 'Bodega o proyecto no proporcionado.'
                ], 400);
            }
        }
        
        // Verifica si el cliente proporcionó el ID del proveedor.
        if ($solicitud->filled('proveedor_id')) {
            // Obtiene el registro del proveedor.
            $proveedor = Proveedor::find($solicitud->proveedor_id);
            
            // Verifica si se encontró el proveedor.
            if ($proveedor) {
                // Condiciona la consulta de unidades, trayéndose solo aquellas que estén asociadas al proveedor.
                $consulta = $consulta->where('proveedor_id', $proveedor->id);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
                return response([
                    'message' => 'Proveedor no encontrado.'
                ], 404);
            }
        }
        
        // Verifica si se ha especificado la vida útil del producto.
        // El valor por defecto (sin especificar) es 0.
        if ($producto->anos_vida_util > 0) {
            // Verifica si tiene el registro de la bodega.
            if ($bodega) {
                // Prepara la consulta para calcular el valor residual (valor de adquisición menos valor depreciado) de las unidades, siguiendo la siguiente fórmula:
                // R=A-(A/V)*(D/365), en donde A corresponde al valor de adquisición, V a los años de vida útil y D a los días en uso.
                $consulta = $consulta->selectRaw('CAST((unidades.valor_adquisicion - ((unidades.valor_adquisicion / ?) * (unidades.dias_en_uso / 365))) AS DECIMAL(15, 2)) AS valor_residual', array($producto->anos_vida_util));
            } else {
                // Prepara la consulta para calcular el valor residual (valor de adquisición menos valor depreciado) de las unidades, siguiendo la siguiente fórmula:
                // R=A-(A/V)*(DA/365), en donde A corresponde al valor de adquisición, V a los años de vida útil, D a los días en uso acumulados.
                
                // Los días en uso acumulados se calculan de la siguiente manera: DA=U+P, en donde U corresponde a los días en uso registrados en la base de datos y
                // P a los días trasncurridos desde la fecha en el que la unidad entró en el proyecto.
                
                $consulta = $consulta->selectRaw('unidades.dias_en_uso + TIMESTAMPDIFF(DAY, unidades.fecha_ingreso_proyecto, NOW()) AS dias_en_uso_acumulado, CAST((unidades.valor_adquisicion - ((unidades.valor_adquisicion / ?) * ((SELECT dias_en_uso_acumulado) / 365))) AS DECIMAL(15, 2)) AS valor_residual', array($producto->anos_vida_util));
            }
        } else {
            // Como no se ha especificado la vida útil del producto, el valor residual (valor de adquisición menos valor depreciado) de la unidad será igual
            // al valor de adquisición.
            $consulta = $consulta->selectRaw('unidades.valor_adquisicion AS valor_residual');
        }
        
        // Prepara la consulta para obetener los datos básicos de los proveedores de las unidades.
        
        // Prepara la consulta para calcular el porcentaje depreciado de la unidad, siguiendo la siguiente fórmula:
        // PD = ((A-R)/A)*100, en donde A corresponde al valor de adquisición y R al valor residual.
        // El cálculo solo se realiza cuando se ha especificado el valor de adquisición de la unidad; en caso contrario, se deja como 0.
        
        $consulta = $consulta->selectRaw('empresas.nombre AS proveedor_nombre, CASE WHEN unidades.valor_adquisicion > 0 THEN FLOOR(((unidades.valor_adquisicion - (SELECT valor_residual)) / unidades.valor_adquisicion) * 100) ELSE 0 END AS depreciacion')
                             ->where('dado_de_baja', $dado_de_baja)
                             ->leftJoin('proveedores', 'proveedores.id', '=', 'unidades.proveedor_id')
                             ->leftJoin('empresas', 'empresas.id', '=', 'proveedores.empresa_id');
                             
        // Verifica si se solcitó exportar la consulta realizada.
        if ($solicitud->exportar) {
            // Inicia una nueva instancia de la clase encargada de dicho proceso.
            // Se le proporciona el resultado de la consulta.
            $exportar = new UnidadesExport($consulta->get());
            
            // Retorna el archivo generado.
            return Excel::download($exportar, 'unidades.xlsx');
        } else {
            // Verifica si se solicitó la búsqueda de una cadena de texto.
            if ($solicitud->filled('buscar')) {
                // Convierte la cadena en un arreglo, separando cada término.
                $terminos = explode(' ', $solicitud->buscar);
                
                // Define las columnas en las que se realizará la búsqueda.
                $columnas = ['unidades.numero_serie', 'unidades.motivo_de_baja', 'empresas.nombre'];
                
                // Recorre el arreglo de los términos.
                foreach ($terminos as $termino) {
                    // Busca los registros de la consulta que contenga el término iterado.
                    $consulta = $consulta->where(function ($sub_consulta) use ($columnas, $termino) {
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
    
    
    /**
     * Obtiene el registro de una unidad.
     */
    public function ver(Request $solicitud)
    {
        // Obtiene el registro de la unidad con el ID proporcionado por el cliente.
        $unidad = Unidad::where('id', $solicitud->id)->with(['producto', 'producto.marca', 'producto.marca.fabricante.empresa', 'proveedor', 'proveedor.empresa'])->first();
        
        // Verifica si se encontró la unidad.
        if ($unidad) {
            // Retorna el registro de la unidad.
            return response($unidad, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
            return response([
                'message' => 'Unidad no encontrada.'
            ], 404);
        }
    }
    
    
    /**
     * Edita el registro de una unidad.
     * Retorna el registro actualizado.
     */    
    public function editar(Request $solicitud)
    {
        // Obtiene el registro de la unidad con el ID proporcionado por el cliente.
        $unidad = Unidad::find($solicitud->id);
        
        // Verifica si se encontró el registro de la unidad.
        if ($unidad) {
            // Valida los campos proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'proveedor_id' => 'sometimes|integer|gt:0',
                'numero_serie' => [ 'required', 'string', 'max:255', Rule::unique('unidades', 'numero_serie')->ignore($unidad->id, 'id') ],
                'fecha_adquisicion' => 'nullable|date',
                'valor_adquisicion' => 'nullable|numeric',
                'dias_en_uso' => 'nullable|integer|min:0',
                'restaurar' => 'sometimes|boolean'
            ]);
            
            // Define la variable que contendrá el registro del proveedor solicitado.
            $proveedor = null;
            
            // Verifica si se proporcionó el ID de un proveedor.
            if ($solicitud->filled('proveedor_id')) {
                // Obtiene el registro del proveedor.
                $proveedor = Proveedor::find($solicitud->proveedor_id);
                
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
                if (!$proveedor) {
                    return response([
                        'message' => 'Proveedor no encontrado.'
                    ], 404);
                }
            }
            
            // Verifica si solicitó restaurar la unidad, en caso de que haya sido dada de baja.
            if ($solicitud->restaurar && $unidad->dado_de_baja) {
                // Marca la unidad como disponible / activa.
                $unidad->dado_de_baja = 0;
                
                // Retira la fecha y el motivo de baja.
                $unidad->fecha_de_baja = null;
                $unidad->motivo_de_baja = null;
            }
            
            // Rellena los atributos de la unidad con los campos proporcionados por el cliente.
            $unidad->fill($solicitud->except(['bodega_id', 'producto_id']));
            
            // Guarda los cambios en la base de datos.
            $unidad->save();
            
            // Actualiza la instancia Unidad con el respectivo registro de la base de datos.
            $unidad->refresh();
            
            // Retorna el registro de la unidad actualizada.
            return response($unidad, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
            return response([
                'message' => 'Unidad de producto no encontrada.'
            ], 404);
        }
    }
    
    
    /**
     * Mueve una o varias unidades a otra bodega o proyecto.
     */
    public function mover(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'destino' => 'required|in:bodega,proyecto',
            'destino_id' => 'required|integer|gt:0',
            'unidades_id' => 'sometimes|string|regex:/^\d+(?:,\d+)*$/'
        ]);
        
        // Define la variable que contendrá el arreglo de ID de unidades proporcionadas por el cliente.
        $unidades_id = null;
        
        // Verifica si el cliente proporcinó el ID de una unidad.
        if ($solicitud->id) {
            // Convierte la cadena del ID de la unidad en un arreglo.
            $unidades_id = explode(',', $solicitud->id);
        } else {
            // Verifica si se proporcionó el ID de varias unidades.
            if ($solicitud->filled('unidades_id')) {
                // Convierte la cadena de ID de las unidades en un arreglo.
                $unidades_id = explode(',', $solicitud->unidades_id);
            } else {
                // Genera la respuesta HTTP en caso de que el cliente no haya proporcionado las unidades.
                return response([
                    'message' => 'Unidades no proporcionadas.'
                ], 400);
            }
        }
        
        // Constata si se va a gestionar más de una unidad.
        $en_bloque = count($unidades_id) > 1;
        
        // Define las variables que contendrán los registros de la bodega o el proyecto.
        $bodega = null;
        $proyecto = null;
        
        // Verifica si el destino solicitado de la unidad es una bodega.
        if ($solicitud->destino === 'bodega') {
            // Obtiene el registro de la bodega.
            $bodega = Bodega::find($solicitud->destino_id);
            
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
            if (!$bodega) {
                return response([
                    'message' => 'Bodega no encontrada.'
                ], 404);
            }
        } else {
            // Obtiene el registro del proyecto.
            $proyecto = Proyecto::find($solicitud->destino_id);
            
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
            if (!$proyecto) {
                return response([
                    'message' => 'Proyecto no encontrado.'
                ], 404);
            }
        }
        
        // Recorre el arreglo de ID de las unidades.
        foreach ($unidades_id as $id) {
            // Obtiene el registro de la unidad con el ID iterado.
            $unidad = Unidad::find($id);
            
            // Verifica si se encontró la unidad.
            if ($unidad) {
                // Verifica si el destino solicitado de la unidad es una bodega.
                if ($bodega) {
                    // Verifica si el usuario no está intentado mover la unidad a la misma bodega.
                    if ($unidad->bodega_id !== $solicitud->destino_id) {
                        // Obtiene el registro de la bodega.
                        $bodega = Bodega::find($solicitud->destino_id);
                        
                        // Verifica si la unidad está en un proyecto.
                        if ($unidad->proyecto_id) {
                            // Retira la unidad del proyecto.
                            $unidad->proyecto_id = null;
                            
                            // Actualiza los días en uso de la unidad, sumándole los dias que duró en uso en el proyecto.
                            $unidad->dias_en_uso = $unidad->dias_en_uso + now()->diffInDays($unidad->fecha_ingreso_proyecto);
                            
                            // Retira la fecha de ingreso de la unidad en el proyecto.
                            $unidad->fecha_ingreso_proyecto = null;
                        }
                        
                        // Agrega la unidad a la bodega destino.
                        $unidad->bodega_id = $bodega->id;
                        
                        // Guarda los cambios en la base de datos.
                        $unidad->save();
                        
                        // Verifica que no se estén gestionando múltiples undidades.
                        if (!$en_bloque) {
                            // Genera la respuesta HTTP del resultado del proceso.
                            return response([
                                'message' => 'Movido a bodega.'
                            ], 200);
                        }
                    } else {
                        // Verifica que no se estén gestionando múltiples undidades.
                        if (!$en_bloque) {
                            // Genera la respuesta HTTP en caso de que se esté intentando mover la unidad a la misma bodega.
                            return response([
                                'message' => 'Ya se encuentra en bodega.'
                            ], 200);
                        }
                    }
                } else {
                    // Verifica si el usuario no está intentado mover la unidad al mismo proyecto.
                    if ($unidad->proyecto_id !== $solicitud->destino_id) {
                        // Verifica si la unidad está en un proyecto.
                        if ($unidad->proyecto_id) {
                            // Actualiza los días en uso de la unidad, sumándole los dias que duró en uso en el proyecto.
                            $unidad->dias_en_uso = $unidad->dias_en_uso + now()->diffInDays($unidad->fecha_ingreso_proyecto);
                        } else {
                            // Retira la unidad de la bodega.
                            $unidad->bodega_id = null;
                        }
                        
                        // Agrega la unidad al proyecto.
                        $unidad->proyecto_id = $proyecto->id;
                        
                        // Registra la fecha de ingreso de la unidad en el proyecto.
                        $unidad->fecha_ingreso_proyecto = Carbon::now();
                        
                        // Guarda los cambios en la base de datos.
                        $unidad->save();
                        
                        // Verifica que no se estén gestionando múltiples undidades.
                        if (!$en_bloque) {
                            // Genera la respuesta HTTP del resultado del proceso.
                            return response([
                                'message' => 'Movido a proyecto.'
                            ], 200);
                        }
                    } else {
                        // Verifica que no se estén gestionando múltiples undidades.
                        if (!$en_bloque) {
                            // Genera la respuesta HTTP en caso de que se esté intentando mover la unidad al mismo proyecto.
                            return response([
                                'message' => 'Ya se encuentra en proyecto.'
                            ], 200);
                        }
                    }
                }
            } else {
                // Verifica que no se estén gestionando múltiples undidades.
                if (!$en_bloque) {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
                    return response([
                        'message' => 'Unidad no encontrada.'
                    ], 404);
                }
            }
        }
        
        // Verifica que se estén gestionando múltiples undidades.
        if ($en_bloque) {
            // Genera la respuesta HTTP del resultado del proceso.
            return response([
                'message' => 'Unidades movidas a ' . $solicitud->destino . '.'
            ], 200);
        }
    }
    
    
    /**
     * Da de baja/Restaura una unidad.
     */
    public function darDeBaja(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'unidades_id' => 'sometimes|string|regex:/^\d+(?:,\d+)*$/',
            'motivo_de_baja' => 'nullable|string|max:500'
        ]);
        
        // Define la variable que contendrá el arreglo de ID de unidades proporcionadas por el cliente.
        $unidades_id = null;
        
        // Verifica si el cliente proporcinó el ID de una unidad.
        if ($solicitud->id) {
            // Convierte la cadena del ID de la unidad en un arreglo.
            $unidades_id = explode(',', $solicitud->id);
        } else {
            // Verifica si se proporcionó el ID de varias unidades.
            if ($solicitud->filled('unidades_id')) {
                // Convierte la cadena de ID de las unidades en un arreglo.
                $unidades_id = explode(',', $solicitud->unidades_id);
            } else {
                // Genera la respuesta HTTP en caso de que el cliente no haya proporcionado las unidades.
                return response([
                    'message' => 'Unidades no proporcionadas.'
                ], 400);
            }
        }
        
        // Constata si se va a gestionar más de una unidad.
        $en_bloque = count($unidades_id) > 1;
        
        // Recorre el arreglo de ID de las unidades.
        foreach ($unidades_id as $id) {
            // Obtiene el registro de la unidad con el ID iterado.
            $unidad = Unidad::find($id);
            
            // Verifica si se encontró la unidad.
            if ($unidad) {
                // Da de baja/Restaura la unidad, estableciendo/retirando el motivo y la fecha de baja.
                $unidad->update([
                    'dado_de_baja' => $unidad->dado_de_baja ? 0 : 1,
                    'motivo_de_baja' => $unidad->dado_de_baja ? null : ($solicitud->motivo_de_baja ?: null),
                    'fecha_de_baja' => $unidad->dado_de_baja ? null : Carbon::now()
                ]);
                
                // Verifica que no se estén gestionando múltiples undidades.
                if (!$en_bloque) {
                    // Verifica si la unidad fue dada de baja.
                    if ($unidad->dado_de_baja) {
                        // Genera la respuesta HTTP del resultado del proceso.
                        return response([
                            'message' => 'Unidad dada de baja.' 
                        ], 200);
                    } else {
                        // Genera la respuesta HTTP del resultado del proceso en caso de que haya sido restaurada.
                        return response([
                            'message' => 'Unidad restaurada.'
                        ], 200);
                    }
                }
            } else {
                // Verifica que no se estén gestionando múltiples undidades.
                if (!$en_bloque) {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
                    return response([
                        'message' => 'Unidad no encontrada.'
                    ], 404);
                }
            }
        }
        
        // Verifica que se estén gestionando múltiples undidades.
        if ($en_bloque) {
            // Genera la respuesta HTTP del resultado del proceso.
            return response([
                'message' => 'Unidades actualizadas.'
            ], 200);
        }
    }
    
    
    /**
     * Elimina una o varias unidades.
     */
    public function eliminar (Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'unidades_id' => 'sometimes|string|regex:/^\d+(?:,\d+)*$/'
        ]);
        
        // Define la variable que contendrá el arreglo de ID de unidades proporcionadas por el cliente.
        $unidades_id = null;
        
        // Verifica si el cliente proporcinó el ID de una unidad.
        if ($solicitud->id) {
            // Convierte la cadena del ID de la unidad en un arreglo.
            $unidades_id = explode(',', $solicitud->id);
        } else {
            // Verifica si se proporcionó el ID de varias unidades.
            if ($solicitud->filled('unidades_id')) {
                // Convierte la cadena de ID de las unidades en un arreglo.
                $unidades_id = explode(',', $solicitud->unidades_id);
            } else {
                // Genera la respuesta HTTP en caso de que el cliente no haya proporcionado las unidades.
                return response([
                    'message' => 'Unidades no proporcionadas.'
                ], 400);
            }
        }
        
        // Constata si se va a gestionar más de una unidad.
        $en_bloque = count($unidades_id) > 1;
        
        // Recorre el arreglo de ID de las unidades.
        foreach ($unidades_id as $id) {
            // Obtiene el registro de la unidad con el ID iterado.
            $unidad = Unidad::find($id);
            
            // Verifica si se encontró la unidad.
            if ($unidad) {
                // Elimina el registro de la unidad.
                $unidad->delete();
                
                // Verifica que no se estén gestionando múltiples undidades.
                if (!$en_bloque) {
                    return response([
                        'message' => 'Unidad eliminada.'
                    ], 200);
                }
            } else {
                // Verifica que no se estén gestionando múltiples undidades.
                if (!$en_bloque) {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la unidad.
                    return response([
                        'message' => 'Unidad no encontrada.'
                    ], 404);
                }
            }
        }
        
        // Verifica que no se estén gestionando múltiples undidades.
        if (!$en_bloque) {
            // Genera la respuesta HTTP del resultado del proceso.
            return response([
                'message' => 'Unidades eliminadas.'
            ], 200);
        }
    }
}