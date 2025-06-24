<?php

namespace App\Http\Controllers;

use App\Exports\ProveedoresExport;
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

class ProveedorController extends Controller
{
    use UbicacionTrait;
    
    /**
     * Registra un proveedor.
     * Retorna el proveedor recién registrado.
     */
    public function registrar (Request $solicitud)
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
            'guardarComoFabricante' => 'sometimes|accepted'
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
        
        // Inicia una nueva instancia del modelo Proveedor.
        $proveedor = new Proveedor;
        
        // Relaciona la instancia recién creada con los datos de la empresa recién registrada.
        $proveedor->empresa_id = $empresa->id;
        
        // Inserta el registro en la base de datos.
        $proveedor->save();
        
        // Actualiza la instancia Proveedor con el respectivo registro de la base de datos.
        $proveedor->refresh();
        
        // Verifica si se solicitó registrar un fabricante con los datos del mismo proveedor.
        if ($solicitud->guardarComoFabricante) {
            // Inicia una nueva instancia del modelo Fabricante.
            $fabricante = new Fabricante;
            
            // Vincula los datos de la empresa con el fabricante.
            $fabricante->empresa_id = $empresa->id;
            
            // Inserta el registro en la base de datos.
            $fabricante->save();
        }
        
        // Combina el registro del proveedor con los datos de la empresa relacionada.
        $registro = collect($proveedor->empresa)->merge($proveedor);
        
        // Retorna el proveedor recién registrado.
        return response($registro, 201);
    }
    
    
    /**
     * Retorna los proveedores registrados, teniendo en cuenta los parámetros de consulta proporcionados por el cliente.
     */
    public function listar(Request $solicitud)
    {
        // Verifica si se requiere una lista simple, es decir, sin estar paginada y con solo los nombres e ID de los registros.
        if ($solicitud->lista === 'simple') {
            // Obtiene los nombres y los ID de todos los proveedores.
            $proveedores = Proveedor::select('proveedores.id', 'empresas.nombre')
                                        ->leftJoin('empresas', 'empresas.id', '=', 'proveedores.empresa_id')
                                        ->get();
                                        
            // Retorna la lista de proveedores.
            return response($proveedores, 200);
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
            $consulta = Proveedor::selectRaw('proveedores.*, empresas.nombre, empresas.nit, empresas.logotipo_url, CONCAT_WS(", ", paises.nombre, departamentos.nombre, municipios.nombre) AS ubicacion')
                                  ->leftJoin('empresas', 'empresas.id', '=', 'proveedores.empresa_id')
                                  ->leftJoin('paises', 'paises.id', '=', 'empresas.pais_id')
                                  ->leftJoin('departamentos', 'departamentos.id', '=', 'empresas.departamento_id')
                                  ->leftJoin('municipios', 'municipios.id', '=', 'empresas.municipio_id');
            
            // Verifica si se proporcionó el ID de la bodega.
            if ($solicitud->filled('bodega_id')) {
                // Obtiene el registro de la bodega.
                $bodega = Bodega::find($solicitud->bodega_id);
                
                // Verifica si se encontró la bodega.
                if ($bodega) {
                    // Verifica si se solicitaron los proveedores con unidades asociadas a la bodega.
                    if ($solicitud->en_bodega) {
                        // Prepara la consulta para obtener los proveedores con unidades disponibles que estén en la bodega.
                        // También trae consigo el total de unidades.
                        $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)->where('dado_de_baja', 0);
                                             }])
                                             ->whereHas('unidades', function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)->where('dado_de_baja', 0);
                                             });
                    } else {
                        // Prepara la consulta para obtener los proveedores que no disponen de unidades en la bodega.
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
                        // Verifica si se solicitaron los proveedores con unidades asociadas al proyecto.
                        if ($solicitud->en_proyecto) {
                            // Prepara la consulta para obtener los proveedores con unidades empleadas en el proyecto.
                            // También trae consigo el total de unidades.
                            $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($proyecto) {
                                                     $unidades->where('proyecto_id', $proyecto->id)->where('dado_de_baja', 0);
                                                 }])
                                                 ->whereHas('unidades', function ($unidades) use ($proyecto) {
                                                     $unidades->where('proyecto_id', $proyecto->id)->where('dado_de_baja', 0);
                                                 });
                        } else {
                            // Prepara la consulta para obtener los proveedores que no disponen de unidades en el proyecto.
                            $consulta = $consulta::selectRaw('0 AS unidades_count')
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
                $exportar = new ProveedoresExport($consulta->get());
                
                // Retorna el archivo generado.
                return Excel::download($exportar, 'proveedores.xlsx');
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
     * Obtiene el registro de un proveedor.
     */
    public function ver (Request $solicitud)
    {
        // Verifica si se solicitó el registro del proveedor a través del ID de una empresa.
        if ($solicitud->route()->getName() === 'empresa') {
            // Obtiene el registro de la empresa detrás del proveedor con el ID proporcionado por el cliente.
            $empresa = Empresa::find($solicitud->id);
            
            // Verifica si se encontró la empresa y si hay un proveedor relacionado con esta.
            if ($empresa && $empresa->proveedor()->exists()) {
                // Combina el registro del proveedor con los datos de la empresa relacionada.
                $registro = collect($empresa)->merge($empresa->proveedor);
                
                // Retorna el resultado.
                return response($registro, 200);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
                return response([
                    'message' => 'Proveedor no encontrado.'
                ], 404);
            }
        } else { 
            // Obtiene el registro del proveedor con el ID proporcionado por el cliente.
            $proveedor = Proveedor::where('id', $solicitud->id)->with(['empresa.pais', 'empresa.departamento', 'empresa.municipio', 'empresa.barrio'])->first();
            
            // Verifica si se encontró el proveedor.
            if ($proveedor) {
                // Combina el registro del proveedor con los datos de la empresa relacionada.
                $registro = collect($proveedor->empresa)->merge($proveedor);
                
                // Retorna el resultado.
                return response($registro, 200);
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
                return response([
                    'message' => 'Proveedor no encontrado.'
                ], 404);
            }
        }
    }
    
    
    /**
     * Edita el registro de un proveedor.
     * Retorna el registro actualizado.
     */
    public function editar (Request $solicitud)
    {
        // Obtiene el registro del proveedor con el ID proporcionado por el cliente.
        $proveedor = Proveedor::find($solicitud->id);
        
        // Verifica si se encontró el registro del proveedor.
        if ($proveedor) {
            // Valida los campos proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'nombre' => 'required|string|max:255|unique:empresas,nombre,' . $proveedor->empresa_id . ',id',
                'nit' => 'nullable|string|max:255|unique:empresas,nit,' . $proveedor->empresa_id . ',id',
                'descripcion' => 'nullable|string|max:500',
                'pais' => 'nullable|required_with:departamento|string|max:255',
                'departamento' => 'nullable|string|required_with:municipio|max:255',
                'municipio' => 'nullable|string|required_with:barrio|string|max:255',
                'barrio' => 'nullable|string|max:255',
                'direccion' => 'nullable|string|max:255',
                'pagina_web' => 'nullable|string|url|max:255',
                'logotipo' => 'nullable|image:jpg,jpeg,png',
                'guardarComoFabricante' => 'sometimes|accepted'
            ]);
            
            // Obtiene el registro de la empresa relacionada con el proveedor.
            $empresa = Empresa::find($proveedor->empresa_id);
            
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
                    
                    // Verifica si el registro del producto tiene una imagen.
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
            
            // Actualiza el atributo "updated_at" del Proveedor.
            $proveedor->touch();
            
            // Actualiza la instancia Proveedor con el respectivo registro de la base de datos.
            $proveedor->refresh();
            
            // Verifica si se solicitó registrar un fabricante con los datos del mismo proveedor.
            if ($solicitud->guardarComoFabricante) {
                // Busca la existencia de algún fabricante que ya comparta los datos del proveedor.
                $fabricante = Fabricante::where('empresa_id', $empresa->id);
                // Constata que no exista fabricante alguno.
                if (!$fabricante->exists()) {
                    // Inicia una nueva instancia del modelo Fabricante.
                    $fabricante = new Fabricante;
                    
                    // Vincula los datos de la empresa con el fabricante.
                    $fabricante->empresa_id = $empresa->id;
                    
                    // Inserta el registro en la base de datos.
                    $fabricante->save();
                }
            }
            
            // Retorna el registro del proveedor.
            return response($proveedor, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
            return response([
                'message' => 'Proveedor no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Elimina el registro de un proveedor.
     */
    public function eliminar (Request $solicitud)
    {
        // Obtiene el registro del proveedor con el ID proporcionado por el cliente.
        $proveedor = Proveedor::find($solicitud->id);
        
        // Verifica si se encontró el registro del proveedor.
        if ($proveedor) {
            // Elimina las relaciones que el proveedor pueda tener con las unidades.
            $proveedor->unidades()->update(['proveedor_id' => null]);
            
            // Obtiene el registro de la empresa relacionada con el proveedor.
            $empresa = Empresa::find($proveedor->empresa_id);
            
            // Verifica si la empresa no está relacionada con un fabricante.
            if (!$empresa->fabricante()->exists()) {
                // Verifica si el registro de la empresa tiene una imagen.
                if ($empresa->logotipo_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $empresa->logotipo_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                }
                
                // Elimina el registro de la empresa.
                $empresa->delete();
            }
            
            // Elimina el registro del proveedor.
            $proveedor->delete();
            
            // Genera la respuesta HTTP del resultado de la operación.
            return response([
                'message' => 'Proveedor eliminado.'
            ], 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
            return response([
                'message' => 'Proveedor no encontrado.'
            ], 404);
        }
    }
}