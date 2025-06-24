<?php

namespace App\Http\Controllers;

use App\Exports\FabricantesExport;
use App\Models\Bodega;
use App\Models\Empresa;
use App\Models\Fabricante;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Traits\UbicacionTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class FabricanteController extends Controller
{
    use UbicacionTrait;
    
    /**
     * Registra un fabricante.
     * Retorna el fabricante recién registrado.
     */
    public function registrar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'nombre' => 'required|string|max:255|unique:empresas,nombre',
            'nit' => 'nullable|string|max:255|unique:empresas,nit',
            'descripcion' => 'nullable|string|max:500',
            'pais' => 'nullable|required_with:departamento|string|max:255',
            'departamento' => 'nullable|string|required_with:municipio|max:255',
            'municipio' => 'nullable|string|required_with:barrio|string|max:255',
            'barrio' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'pagina_web' => 'nullable|string|url|max:255',
            'logotipo' => 'nullable|image:jpg,jpeg,png',
            'guardarComoProveedor' => 'sometimes|accepted'
        ]);
        
        // Inicia una nueva instancia del modelo Empresa.
        $empresa = new Empresa;
        
        // Rellena los atributos de la instancia recién creada con los campos proporcionados por el cliente.
        $empresa->fill($solicitud->all());
        
        // Verifica si se proporcionó el archivo de una imagen.
        if ($solicitud->hasFile('logotipo')) {
            // Verifica si el archivo proporcionado es válido.
            if ($solicitud->file('logotipo')->isValid()) {
                // Obtiene el archivo.
                $archivo = $solicitud->file('logotipo');
                
                // Genera la ruta para la imagen.
                $ruta = $archivo->hashName('public/empresas');
                
                // Redimensiona la imagen.
                $imagen = Image::make($archivo)->widen(160);
                
                // Guarda la imagen en el almacenamiento local.
                Storage::put($ruta, (string) $imagen->encode());
                
                // Guarda la URL de la imagen en la instancia Empresa.
                $empresa->logotipo_url = Storage::url($ruta);
            } else {
                // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                return response([
                    'message' => 'Archivo no válido.'
                ], 422);
            }
        }
        
        // Inserta el registro en la base de datos.
        $empresa->save();
        
        // Establece la ubicación de la empresa recién registrada.
        $this->establecerUbicacion($empresa, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
        
        // Actualiza la instancia Empresa con el respectivo registro de la base de datos.
        $empresa->refresh();
        
        // Inicia una nueva instancia del modelo Fabricante.
        $fabricante = new Fabricante;
        
        // Relaciona la instancia recién creada con los datos de la empresa recién registrada.
        $fabricante->empresa_id = $empresa->id;
        
        // Inserta el registro en la base de datos.
        $fabricante->save();
        
        // Actualiza la instancia Fabricante con el respectivo registro de la base de datos.
        $fabricante->refresh();
        
        // Verifica si se solicitó registrar un proveedor con los datos del mismo fabricante.
        if ($solicitud->guardarComoProveedor) {
            // Inicia una nueva instancia del modelo Proveedor.
            $proveedor = new Proveedor;
            
            // Vincula los datos de la empresa con el proveedor.
            $proveedor->empresa_id = $empresa->id;
            
            // Inserta el registro en la base de datos.
            $proveedor->save();
        }
        
        // Combina el registro del fabricante con los datos de la empresa relacionada.
        $registro = collect($fabricante->empresa)->merge($fabricante);
        
        // Retorna el fabricante recién registrado.
        return response($registro, 201);
    }
    
    
    /**
     * Retorna los fabricantes registrados, teniendo en cuenta los parámetros de consulta proporcionados por el cliente.
     */
    public function listar(Request $solicitud)
    {
        // Verifica si se requiere una lista simple, es decir, sin estar paginada y con solo los nombres e ID de los registros.
        if ($solicitud->lista === 'simple') {
            // Obtiene los nombres y los ID de todos los fabricantes.
            $fabricantes = Fabricante::select('fabricantes.id', 'empresas.nombre')
                                ->leftJoin('empresas', 'empresas.id', '=', 'fabricantes.empresa_id')
                                ->get();
                                
            // Retorna la lista de fabricantes.
            return response($fabricantes, 200);
        } else {
            // Valida los parámetros proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'bodega_id' => 'sometimes|required_without:proyecto_id|integer|gt:0',
                'proyecto_id' => 'sometimes|required_without:bodega_id|integer|gt:0',
                'buscar' => 'sometimes|string',
                'ordenar_por' => 'sometimes|string|in:nombre,nit,ubicacion,unidades_count,created_at',
                'ordenar_en' => 'sometimes|string|in:asc,desc',
                'por_pagina' => 'sometimes|integer|between:1,300',
                'en_bodega' => 'sometimes|boolean',
                'en_proyecto' => 'sometimes|boolean'
            ]);
            
            // Define los valores por defecto de los parámetros de la consulta SQL.
            $ordenar_por = $solicitud->ordenar_por ?: 'nombre'; // Nombre de la columna para la cláusula ORDER BY.
            $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
            $por_pagina = $solicitud->por_pagina ?: 20; // Valor para la cláusula LIMIT.
            
            // Prepara la consulta a realizar.
            $consulta = Fabricante::selectRaw('fabricantes.*, empresas.nombre, empresas.nit, empresas.logotipo_url, CONCAT_WS(", ", paises.nombre, departamentos.nombre, municipios.nombre) AS ubicacion')
                                  ->leftJoin('empresas', 'empresas.id', '=', 'fabricantes.empresa_id')
                                  ->leftJoin('paises', 'paises.id', '=', 'empresas.pais_id')
                                  ->leftJoin('departamentos', 'departamentos.id', '=', 'empresas.departamento_id')
                                  ->leftJoin('municipios', 'municipios.id', '=', 'empresas.municipio_id');
            
            // Verifica si se proporcionó el ID de la bodega.
            if ($solicitud->filled('bodega_id')) {
                // Obtiene el registro de la bodega.
                $bodega = Bodega::find($solicitud->bodega_id);
                // Verifica si se encontró la bodega.
                if ($bodega) {
                    // Verifica si se solicitaron los fabricantes con unidades asociadas a la bodega.
                    if ($solicitud->en_bodega) {
                        // Prepara la consulta para obtener los fabricantes con unidades disponibles que estén en la bodega.
                        $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)->where('dado_de_baja', 0);
                                             }])
                                             ->whereHas('unidades', function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)->where('dado_de_baja', 0);
                                             });
                    } else {
                        // Prepara la consulta para obtener los fabricantes que no disponen de unidades en la bodega.
                        $consulta = $consulta->selectRaw('0 AS unidades_count')
                                             ->whereDoesntHave('unidades', function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)
                                                          ->where('dado_de_baja', 0);
                                             });
                    }
                } else {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro de la bodega.
                    return response([
                        'message' => 'Bodega no encontrada.'
                    ], 404);
                }
            } else {
                // Verifica si se proporcionó el ID del proyecto.
                if ($solicitud->filled('proyecto_id')) {
                    // Obtiene el registro del proyecto.
                    $proyecto = Proyecto::find($solicitud->proyecto_id);
                    
                    // Verifica si se encontró el proyecto.
                    if ($proyecto) {
                        // Verifica si se solicitaron los fabricantes con unidades asociadas al proyecto.
                        if ($solicitud->en_proyecto) {
                            // Prepara la consulta para obtener los fabricantes con unidades empleadas en el proyecto.
                            $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($proyecto) {
                                                     $unidades->where('proyecto_id', $proyecto->id)->where('dado_de_baja', 0);
                                                 }])
                                                 ->whereHas('unidades', function ($unidades) use ($proyecto) {
                                                     $unidades->where('proyecto_id', $proyecto->id)->where('dado_de_baja', 0);
                                                 });
                        } else {
                            // Prepara la consulta para obtener los fabricantes que no disponen de unidades en el proyecto.
                            $consulta = $consulta->selectRaw('0 AS unidades_count')
                                                 ->whereDoesntHave('unidades', function ($unidades) use ($proyecto) {
                                                     $unidades->where('proyecto_id', $proyecto->id)
                                                              ->where('dado_de_baja', 0);
                                                 });
                        }
                    } else {
                        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
                        return response([
                            'message' => 'Proyecto no encontrado.'
                        ], 404);
                    }
                }
            }
            
            // Verifica si se solcitó exportar la consulta realizada.
            if ($solicitud->exportar) {
                // Inicia una nueva instancia de la clase encargada de dicho proceso.
                // Se le proporciona el resultado de la consulta.
                $exportar = new FabricantesExport($consulta->get());
                
                // Retorna el archivo generado.
                return Excel::download($exportar, 'fabricantes.xlsx');
            } else {
                // Verifica si se solicitó la búsqueda de una cadena de texto.
                if ($solicitud->filled('buscar')) {
                    // Convierte la cadena en un arreglo, separando cada término.
                    $terminos = explode(' ', $solicitud->buscar);
                    
                    // Define las columnas en las que se realizará la búsqueda.
                    $columnas = ['empresas.nombre', 'empresas.nit', 'paises.nombre', 'departamentos.nombre', 'municipios.nombre'];
                    
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
    }
    
    
    /**
     * Obtiene el registro de un fabricante.
     */
    public function ver(Request $solicitud)
    {
        // Verifica si se solicitó el registro del fabricante a través del ID de una empresa.
        if ($solicitud->route()->getName() === 'empresa') {
            // Obtiene el registro de la empresa detrás del fabricante con el ID proporcionado por el cliente.
            $empresa = Empresa::find($solicitud->id);
            
            // Verifica si se encontró la empresa y si hay un fabricante relacionado con esta.
            if ($empresa && $empresa->fabricante()->exists()) {
                // Combina el registro del fabricante con los datos de la empresa relacionada.
                $registro = collect($empresa)->merge($empresa->fabricante);
                
                // Retorna el resultado.
                return response($registro, 200);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
                return response([
                    'message' => 'Fabricante no encontrado.'
                ], 404);
            }
        } else {
            // Obtiene el registro del fabricante con el ID proporcionado por el cliente.
            $fabricante = Fabricante::where('id', $solicitud->id)->with(['empresa.pais', 'empresa.departamento', 'empresa.municipio', 'empresa.barrio'])->first();
            
            // Verifica si se encontró el fabricante.
            if ($fabricante) {
                // Combina el registro del fabricante con los datos de la empresa relacionada.
                $registro = collect($fabricante->empresa)->merge($fabricante);
                
                // Retorna el resultado.
                return response($registro, 200);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
                return response([
                    'message' => 'Fabricante no encontrado.'
                ], 404);
            }
        }
    }
    
    
    /**
     * Edita el registro de un fabricante.
     * Retorna el registro actualizado.
     */
    public function editar(Request $solicitud)
    {
        // Obtiene el registro del fabricante con el ID proporcionado por el cliente.
        $fabricante = Fabricante::find($solicitud->id);
        
        // Verifica si se encontró el registro del fabricante.
        if ($fabricante) {
            // Valida los campos proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'nombre' => 'required|string|max:255|unique:empresas,nombre,' . $fabricante->empresa_id . ',id',
                'nit' => 'nullable|string|max:255|unique:empresas,nit,' . $fabricante->empresa_id . ',id',
                'descripcion' => 'nullable|string|max:500',
                'pais' => 'nullable|required_with:departamento|string|max:255',
                'departamento' => 'nullable|string|required_with:municipio|max:255',
                'municipio' => 'nullable|string|required_with:barrio|string|max:255',
                'barrio' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'pagina_web' => 'nullable|string|url|max:255',
                'logotipo' => 'nullable|image:jpg,jpeg,png',
                'guardarComoProveedor' => 'sometimes|accepted'
            ]);
            
            // Obtiene el registro de la empresa relacionada con el fabricante.
            $empresa = Empresa::find($fabricante->empresa_id);
            
            // Rellena los atributos de la empresa con los campos proporcionados por el cliente.
            $empresa->fill($solicitud->all());
            
            // Verifica si se proporcionó el campo "logotipo".
            if ($solicitud->exists('logotipo')) {
                // Verifica si se proporcionó el archivo de una imagen.
                if ($solicitud->hasFile('logotipo')) {
                    // Verifica si el archivo proporcionado es válido.
                    if ($solicitud->file('logotipo')->isValid()) {
                        // Verifica si el registro de la empresa tiene una imagen;
                        // si la tiene, se procede a eliminarla.
                        if ($empresa->logotipo_url) {
                            // Obtiene la ruta de la imagen.
                            // Para la gestión de los archivos en Laravel, se hace necesario el
                            // reemplazo del nombre del directorio "storage" a "public".
                            $ruta = str_replace('/storage/', '/public/', $empresa->logotipo_url);
                            
                            // Elimina la imagen del almacenamiento local.
                            Storage::delete($ruta);
                        }
                        
                        // Obtiene el archivo.
                        $archivo = $solicitud->file('logotipo');
                        
                        // Genera la ruta para la imagen.
                        $ruta = $archivo->hashName('public/empresas');
                        
                        // Redimensiona la imagen.
                        $imagen = Image::make($archivo)->widen(160);
                        
                        // Guarda la imagen en el almacenamiento local.
                        Storage::put($ruta, (string) $imagen->encode());
                        
                        // Guarda la URL de la imagen en la instancia Empresa.
                        $empresa->logotipo_url = Storage::url($ruta);
                    } else {
                        // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                        return response([
                            'message' => 'Archivo no válido.'
                        ], 422);
                    }
                } else {
                    // Cuando se recibe el campo "logotipo" sin nada en él, el sistema lo interpreta
                    // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                    
                    // Verifica si el registro del fabricante tiene una imagen.
                    if ($empresa->logotipo_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $empresa->logotipo_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                        
                        // Elimina la URL del registro de la empresa.
                        $empresa->logotipo_url = null;
                    }
                }
            }
            
            // Guarda los cambios en la base de datos.
            $empresa->save();
            
            // Establece la ubicación de la empresa.
            $this->establecerUbicacion($empresa, $solicitud->pais, $solicitud->departamento, $solicitud->municipio, $solicitud->barrio);
            
            // Actualiza el atributo "updated_at" del Fabricante.
            $fabricante->touch();
            
            // Actualiza la instancia Fabricante con el respectivo registro de la base de datos.
            $fabricante->refresh();
            
            // Verifica si se solicitó registrar un proveedor con los datos del mismo fabricante.
            if ($solicitud->guardarComoProveedor) {
                // Busca la existencia de algún proveedor que ya comparta los datos del fabricante.
                $proveedor = Proveedor::where('empresa_id', $empresa->id);
                // Constata que no exista proveedor alguno.
                if (!$proveedor->exists()) {
                    // Inicia una nueva instancia del modelo Proveedor.
                    $proveedor = new Proveedor;
                    
                    // Vincula los datos de la empresa con el proveedor.
                    $proveedor->empresa_id = $empresa->id;
                    
                    // Inserta el registro en la base de datos.
                    $proveedor->save();
                }
            }
            
            // Retorna el registro del fabricante.
            return response($fabricante, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
            return response([
                'message' => 'Fabricante no encontrado.'
            ], 404);
        }
    } 
    
    
    /**
     * Elimina el registro de un fabricante.
     */
    public function eliminar(Request $solicitud)
    {
        // Obtiene el registro del fabricante con el ID proporcionado por el cliente.
        $fabricante = Fabricante::find($solicitud->id);
        
        // Verifica si se encontró el registro del fabricante.
        if ($fabricante) {
            // Verifica si el fabricante tiene productos asociados a él.
            if ($fabricante->productos()->exists()) {
                // Genera la respuesta HTTP para este caso.
                return response([
                    'message' => 'No se puede eliminar el fabricante mientras existan productos asociados a él.'
                ], 403);
            } else {
                // Obtiene el registro de la empresa relacionada con el fabricante.
                $empresa = Empresa::find($fabricante->empresa_id);
                
                // Verifica si la empresa no está relacionada con un proveedor.
                if (!$empresa->proveedor()->exists()) {
                    // Verifica si el registro de la empresa tiene una imagen.
                    if ($fabricante->logotipo_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $fabricante->logotipo_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                    }
                    
                    // Elimina el registro de la empresa.
                    $empresa->delete();
                }
                
                // Elimina el registro del fabricante.
                $fabricante->delete();
                
                // Genera la respuesta HTTP del resultado de la operación.
                return response([
                    'message' => 'Fabricante eliminado.'
                ], 200);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
            return response([
                'message' => 'Fabricante no encontrado.'
            ], 404);
        }
    }
}