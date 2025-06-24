<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Models\Bodega;
use App\Models\Marca;
use App\Models\Fabricante;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\Unidad;
use App\Traits\NotificacionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class ProductoController extends Controller
{
    use NotificacionTrait;
    
    /**
     * Registra un producto.
     * Retorna el producto recién registrado.
     */
    public function registrar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'sometimes|integer|gt:0',
            'fabricante_id' => 'required|integer|gt:0',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'anos_vida_util' => 'nullable|numeric|min:0',
            'unidades_minimas' => 'sometimes|required_with:bodega_id|integer|min:0',
            'imagen' => 'nullable|image:jpg,jpeg,png'
        ]);
        
        // Obtiene el registro del fabricante.
        $fabricante = Fabricante::find($solicitud->fabricante_id);
        
        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
        if (!$fabricante) {
            return response([
                'message' => 'Fabricante no encontrado.'
            ], 404);
        }
        
        // Crea o actualiza el registro de la marca del producto.
        $marca = Marca::updateOrCreate(
            ['fabricante_id' => $fabricante->id, 'nombre' => $solicitud->marca],
            ['fabricante_id' => $fabricante->id, 'nombre' => $solicitud->marca]
        );
        
        // Obtiene el registro del producto con la marca y el modelo especificado.
        $producto = Producto::where('marca_id', $marca->id)->where('modelo', $solicitud->modelo)->first();
        
        // Verifica si se encontró el registro del producto.
        if ($producto) {
            // Genera la respuesta HTTP en el caso de que se haya encontrado un registro similar.
            return response([
                'message' => 'El producto ya ha sido registrado.'
            ], 403);
        } else {
            // Inicia una nueva instancia del modelo Producto.
            $producto = new Producto;
            
            // Rellena los atributos de la instancia recién creada con los campos proporcionados por el cliente.
            $producto->fill($solicitud->all());
            
            // Asocia el producto con la marca.
            $producto->marca_id = $marca->id;
            
            // Verifica si se proporcionó el archivo de una imagen.
            if ($solicitud->hasFile('imagen')) {
                // Verifica si el archivo proporcionado es válido.
                if ($solicitud->file('imagen')->isValid()) {
                    // Obtiene el archivo.
                    $archivo = $solicitud->file('imagen');
                    
                    // Genera la ruta para la imagen.
                    $ruta = $archivo->hashName('public/productos');
                    
                    // Redimensiona la imagen.
                    $imagen = Image::make($archivo)->widen(160);
                    
                    // Guarda la imagen en el almacenamiento local.
                    Storage::put($ruta, (string) $imagen->encode());
                    
                    // Guarda la URL de la imagen en la instancia Producto.
                    $producto->imagen_url = Storage::url($ruta);
                } else {
                    // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                    return response([
                        'message' => 'Archivo no válido.'
                    ], 422);
                }
            }
            
            // Inserta el registro en la base de datos.
            $producto->save();
            
            // Actualiza la instancia del modelo con el respectivo registro de la base de datos.
            $producto->refresh();
            
            // Obtiene los registros de todas las bodegas.
            $bodegas = Bodega::all();
            
            // Recorre la colección de bodegas.
            $bodegas->each(function ($bodega) use ($solicitud, $producto) {
                // Prepara el arreglo de los atributos que se insertarán en la tabla intermedia de la relación Bodega-Producto.
                $atributos = array('unidades_minimas' => 0);
                
                // Verifica si se especificó el número de unidades mínimas requeridas para el producto recién creado.
                // Verifica si el ID de la bodega proporcionada coincide con el ID de la bodega iterada.
                if ($solicitud->filled('unidades_minimas') && $bodega->id === intval($solicitud->bodega_id)) {
                    // Agrega el campo proporcionado al arreglo de los atributos de la tabla intermedia.
                    $atributos['unidades_minimas'] = $solicitud->unidades_minimas;
                }
                
                // Inserta el registro en la tabla intermedia Bodega-Producto.
                $bodega->productos()->attach($producto->id, $atributos);
            });
            
            // Retorna el producto recién registrado.
            return response($producto, 201);
        }
    }
    
    
    /**
     * Retorna los productos registrados para una bodega, fabricante o proveedor,
     * teniendo en cuenta los parámetros de consulta proporcionados por el cliente.
     */
    public function listar(Request $solicitud)
    {
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'sometimes|required_without:proyecto_id|integer|gt:0',
            'proveedor_id' => 'sometimes|integer|gt:0',
            'fabricante_id' => 'sometimes|integer|gt:0',
            'proyecto_id' => 'sometimes|required_without:bodega_id|integer|gt:0',
            'buscar' => 'sometimes|string',
            'ordenar_por' => 'sometimes|string|in:nombre,fabricante,marca,modelo,unidades_count,created_at',
            'ordenar_en' => 'sometimes|string|in:asc,desc',
            'por_pagina' => 'sometimes|integer|between:1,300',
            'en_bodega' => 'sometimes|required_with:bodega_id|boolean',
            'dado_de_baja' => 'sometimes|boolean',
            'exportar' => 'sometimes|boolean'
        ]);
        
        // Define los valores por defecto de los parámetros de la consulta SQL.
        $ordenar_por = $solicitud->ordenar_por ?: 'nombre'; // Nombre de la columna para la cláusula ORDER BY.
        $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
        $por_pagina = $solicitud->por_pagina ?: 20; // Valor para la cláusula LIMIT.
        $dado_de_baja = $solicitud->dado_de_baja ?: 0; // Valor para condicionar la consulta de las unidades. Puede ser 1 o 0.
        
        // Define las variables que contendrán los registros de la bodega, del proyecto, del proveedor y del fabricante.
        $bodega = null;
        $proyecto = null;
        $proveedor = null;
        $fabricante = null;
        
        // Define el nombre de la columna y su valor a consultar.
        $columna = null; // Puede ser "bodega_id" o "proyecto_id", dependiendo del escenario.
        $columna_id = null; // ID del registro de la bodega o del proyecto, dependiendo del escenario.
        
        // Verifica si se proporcionó el ID de la bodega.
        if ($solicitud->filled('bodega_id')) {
            // Obtiene el registro de la bodega.
            $bodega = Bodega::find($solicitud->bodega_id);
            
            // Verifica si se encontró la bodega.
            if ($bodega) {
                // Define la columna a consultar para este caso.
                $columna = 'bodega_id';
                
                // Define el valor a comparar en la consulta para este caso.
                $columna_id = $bodega->id;
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
                    // Define la columna a consultar para este caso.
                    $columna = 'proyecto_id';
                    
                    // Define el valor a comparar en la consulta para este caso.
                    $columna_id = $proyecto->id;
                } else {
                    // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proyecto.
                    return response([
                        'message' => 'Proyecto no encontrado.'
                    ], 404);
                }
            } else {
                // Genera la respuesta HTTP en caso de que no se haya proporcionado el ID de la bodega o proyecto.
                return response([
                    'message' => 'Bodega o proyecto no proporcionado.'
                ], 400);
            }
        }
        
        // Verifica si se proporcionó el ID del proveedor.
        if ($solicitud->filled('proveedor_id')) {
            // Obtiene el registro del proveedor.
            $proveedor = Proveedor::find($solicitud->proveedor_id);
            
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del proveedor.
            if (!$proveedor) {
                return response([
                    'message' => 'Proveedor no encontrado.'
                ], 404);
            }
        } else {
            // Verifica si se proporcionó el ID del fabricante.
            if ($solicitud->filled('fabricante_id')) {
                // Obtiene el registro del fabricante.
                $fabricante = Fabricante::find($solicitud->fabricante_id);
                
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
                if (!$fabricante) {
                    return response([
                        'message' => 'Fabricante no encontrado.'
                    ], 404);
                }
            }
        }
        
        // Prepara la consulta a realizar.
        $consulta = Producto::selectRaw('productos.*, fabricantes.id AS fabricante_id, fabricantes.id AS fabricante_id, empresas.nombre AS fabricante, marcas.nombre AS marca')
                            ->leftJoin('marcas', 'marcas.id', '=', 'productos.marca_id')
                            ->leftJoin('fabricantes', 'fabricantes.id', '=', 'marcas.fabricante_id')
                            ->leftJoin('empresas', 'empresas.id', '=', 'fabricantes.empresa_id');
        
        // Verifica si se tiene el registro del proveedor.
        if ($proveedor) {
            // Prepara la consulta para obtener los productos con unidades disponibles proveídas por el proveedor.
            $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($solicitud, $columna, $columna_id, $proveedor) {
                                     $unidades->where($columna, $columna_id)
                                              ->where('proveedor_id', $proveedor->id)
                                              ->where('dado_de_baja', $solicitud->dado_de_baja);
                                 }])
                                 ->whereHas('unidades', function ($unidades) use ($solicitud, $columna, $columna_id, $proveedor) {
                                     $unidades->where($columna, $columna_id)
                                              ->where('proveedor_id', $proveedor->id)
                                              ->where('dado_de_baja', $solicitud->dado_de_baja);
                                 });
        } else {
            // Verifica si se tiene el registro del fabricante.
            if ($fabricante) {
                // Prepara la consulta para obtener los productos con unidades disponibles elaboradas por el fabricante.
                $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($solicitud, $columna, $columna_id) {
                                         $unidades->where($columna, $columna_id)
                                                  ->where('dado_de_baja', $solicitud->dado_de_baja);
                                     }])
                                     ->whereHas('unidades', function ($unidades) use ($solicitud, $columna, $columna_id) {
                                         $unidades->where($columna, $columna_id)
                                                  ->where('dado_de_baja', $solicitud->dado_de_baja);
                                     })
                                     ->where('marcas.fabricante_id', $fabricante->id);
            } else {
                // Verifica si se tiene el registro de la bodega.
                if ($bodega) {
                    // Verifica si se solicitaron los productos con unidades asociadas a la bodega.
                    if ($solicitud->en_bodega) {
                        // Prepara la consulta para obtener los productos con unidades disponibles que estén en la bodega.
                        $consulta = $consulta->selectRaw('bodega_producto.unidades_minimas')
                                             ->withCount(['unidades' => function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)
                                                          ->where('dado_de_baja', 0);
                                             }])
                                             ->whereHas('unidades', function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)
                                                          ->where('dado_de_baja', 0);
                                             })
                                             ->join('bodega_producto', function ($union) use ($bodega) {
                                                 $union->on('bodega_producto.producto_id', '=', 'productos.id')
                                                       ->where('bodega_producto.bodega_id', '=', $bodega->id);
                                             });
                    } else {
                        // Prepara la consulta para obtener los productos sin unidades disponibles en la bodega.
                        $consulta = $consulta->selectRaw('0 AS unidades_count')
                                             ->whereDoesntHave('unidades', function ($unidades) use ($bodega) {
                                                 $unidades->where('bodega_id', $bodega->id)
                                                          ->where('dado_de_baja', 0);
                                             })
                                             ->join('bodega_producto', function ($union) use ($bodega) {
                                                 $union->on('bodega_producto.producto_id', '=', 'productos.id')
                                                       ->where('bodega_producto.bodega_id', '=', $bodega->id);
                                             });
                    }
                } else {
                    // Prepara la consulta para obtener los productos con unidades (activas o dadas de baja) que estén en el proyecto.
                    $consulta = $consulta->withCount(['unidades' => function ($unidades) use ($proyecto, $solicitud) {
                                             $unidades->where('proyecto_id', $proyecto->id)
                                                      ->where('dado_de_baja', $solicitud->dado_de_baja);
                                         }])
                                         ->whereHas('unidades', function ($unidades) use ($proyecto, $solicitud) {
                                             $unidades->where('proyecto_id', $proyecto->id)
                                                      ->where('dado_de_baja', $solicitud->dado_de_baja);
                                         });
                }
            }
        }
        
        // Verifica si se solcitó exportar la consulta realizada.
        if ($solicitud->exportar) {
            // Inicia una nueva instancia de la clase encargada de dicho proceso.
            // Se le proporciona el resultado de la consulta.
            $exportar = new ProductosExport($consulta->get());
            
            // Retorna el archivo generado.
            return Excel::download($exportar, 'productos.xlsx');
        } else {
            // Verifica si se solicitó la búsqueda de una cadena de texto.
            if ($solicitud->filled('buscar')) {
                // Convierte la cadena en un arreglo, separando cada término.
                $terminos = explode(' ', $solicitud->buscar);
                
                // Define las columnas en las que se realizará la búsqueda.
                $columnas = ['empresas.nombre', 'marcas.nombre', 'productos.modelo'];
                
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
     * Obtiene el registro de un producto.
     */
    public function ver(Request $solicitud)
    {
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'sometimes|integer|gt:0'
        ]);
        
        // Define la variable que contendrá el registro de la bodega en donde está el producto.
        $bodega = null;
        
        // Define la variable que contendrá el registro del producto.
        $producto = null;
        
        // Verifica si se proporcionó el ID de la bodega.
        if ($solicitud->filled('bodega_id')) {
            // Obtiene el registro de la bodega.
            $bodega = Bodega::find($solicitud->bodega_id);
        }
        
        // Verifica si se tiene el registro de una bodega.
        if ($bodega) {
            // Obtiene el registro del producto con los datos de la relación entre este y la bodega.
            $producto = Producto::withCount(['unidades' => function ($unidades) use ($bodega) {
                        $unidades->where('bodega_id', $bodega->id)
                                 ->where('dado_de_baja', 0);
                    }])
                    ->with(['bodegas' => function ($bodega_producto) use ($bodega) {
                        $bodega_producto->where('bodega_id', $bodega->id);
                    }, 'marca', 'marca.fabricante.empresa'])
                    ->first();
        } else {
            // Obtiene el registro del producto con el ID proporcionado por el cliente.
            $producto = Producto::where('id', $solicitud->id)->with(['bodegas', 'marca', 'marca.fabricante.empresa'])->first();
        }
        
        // Verifica si se encontró el producto.
        if ($producto) {
            // Retorna el registro del producto.
            return response($producto, 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del producto.
            return response([
                'message' => 'Producto no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Edita el registro de un producto.
     * Retorna el registro actualizado.
     */
    public function editar(Request $solicitud)
    {     
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'bodega_id' => 'sometimes|integer|gt:0',
            'fabricante_id' => 'required|integer|gt:0',
            'marca' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'anos_vida_util' => 'nullable|numeric|min:0',
            'unidades_minimas' => 'sometimes|required_with:bodega_id|integer|min:0',
            'imagen' => 'nullable|image:jpg,jpeg,png'
        ]);
        
        // Obtiene el registro del fabricante.
        $fabricante = Fabricante::find($solicitud->fabricante_id);
        
        // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del fabricante.
        if (!$fabricante) {
            return response([
                'message' => 'Fabricante no encontrado.'
            ], 404);
        }
        
        // Crea o actualiza el registro de la marca del producto.
        $marca = Marca::updateOrCreate(
            ['fabricante_id' => $fabricante->id, 'nombre' => $solicitud->marca],
            ['fabricante_id' => $fabricante->id, 'nombre' => $solicitud->marca]
        );
        
        // Obtiene el registro del producto con la marca y el modelo especificado.
        $producto = Producto::whereNot('id', $solicitud->id)->where('marca_id', $marca->id)->where('modelo', $solicitud->modelo)->first();
        
        // Verifica si se encontró el registro del producto.
        if ($producto) {
            // Genera la respuesta HTTP en el caso de que se haya encontrado un registro similar.
            return response([
                'message' => 'Ya existe otro producto con la misma marca y modelo.'
            ], 403);
        } else {
            // Obtiene el registro del producto con el ID proporcionado por el cliente.
            $producto = Producto::find($solicitud->id);
            
            // Verifica si se encontró el registro del producto.
            if ($producto) {
                // Rellena los atributos del producto con los campos proporcionados por el cliente.
                $producto->fill($solicitud->all());
                
                // Guarda el ID de la marca del producto antes de cambiarla.
                $marca_id = $producto->marca_id;
                
                // Asocia el producto con la nueva marca.
                $producto->marca_id = $marca->id;
                
                // Verifica si se proporcionó el campo "imagen".
                if ($solicitud->exists('imagen')) {
                    // Verifica si se proporcionó el archivo de una imagen.
                    if ($solicitud->hasFile('imagen')) {
                        // Verifica si el archivo proporcionado es válido.
                        if ($solicitud->file('imagen')->isValid()) {
                            // Verifica si el registro del producto tiene una imagen;
                            // si la tiene, se procede a eliminarla.
                            if ($producto->imagen_url) {
                                // Obtiene la ruta de la imagen.
                                // Para la gestión de los archivos en Laravel, se hace necesario el
                                // reemplazo del nombre del directorio "storage" a "public".
                                $ruta = str_replace('/storage/', '/public/', $producto->imagen_url);
                                
                                // Elimina la imagen del almacenamiento local.
                                Storage::delete($ruta);
                            }
                            
                            // Obtiene el archivo.
                            $archivo = $solicitud->file('imagen');
                            
                            // Genera la ruta para la imagen.
                            $ruta = $archivo->hashName('public/productos');
                            
                            // Redimensiona la imagen.
                            $imagen = Image::make($archivo)->widen(160);
                            
                            // Guarda la imagen en el almacenamiento local.
                            Storage::put($ruta, (string) $imagen->encode());
                            
                            // Guarda la URL de la imagen en la instancia Producto.
                            $producto->imagen_url = Storage::url($ruta);
                        } else {
                            // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                            return response([
                                'message' => 'Archivo no válido.'
                            ], 422);
                        }
                    } else {
                        // Cuando se recibe el campo "imagen" sin nada en él, el sistema lo interpreta
                        // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                        
                        // Verifica si el registro del producto tiene una imagen.
                        if ($producto->imagen_url) {
                            // Obtiene la ruta de la imagen.
                            // Para la gestión de los archivos en Laravel, se hace necesario el
                            // reemplazo del nombre del directorio "storage" a "public".
                            $ruta = str_replace('/storage/', '/public/', $producto->imagen_url);
                            
                            // Elimina la imagen del almacenamiento local.
                            Storage::delete($ruta);
                            
                            // Elimina la URL del registro del producto.
                            $producto->imagen_url = null;
                        }
                    }
                }
                
                // Guarda los cambios en la base de datos.
                $producto->save();
                
                // Actualiza la instancia Producto con el respectivo registro de la base de datos.
                $producto->refresh();
                
                // Verifica si se proporcionó un número de unidades mínimas requeridas del producto.
                if ($solicitud->filled('unidades_minimas')) {
                    // Actualiza la table intermedia de la relación Bodega-Producto con el valor especificado.
                    $producto->bodegas()->updateExistingPivot($solicitud->bodega_id, array('unidades_minimas' => $solicitud->unidades_minimas));
                    
                    // Constata si hay suficientes existencias del producto en la bodega especificada.
                    $this->constatarExistencias($producto->id, $solicitud->bodega_id);
                }
                
                // Verifica si la marca del producto cambió.
                if ($marca_id !== $producto->marca_id) {
                    // Obtiene el registro de la marca.
                    $marca = Marca::find($marca_id);
                    // Verifica si la marca previa no está asociada con otros productos.
                    if (!$marca->productos()->exists()) {
                        // Elimina la marca.
                        $marca->delete();
                    }
                }
                
                // Retorna el registro del producto actualizado.
                return response($producto, 200);
                
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del producto.
                return response([
                    'message' => 'Producto no encontrado.'
                ], 404);
            }
        }
    }
    
    
    /**
     * Elimina el registro de un producto.
     * Procede únicamente cuando el producto no tiene unidades disponibles asociadas a él.
     */
    public function eliminar(Request $solicitud)
    {
        // Obtiene el registro del producto con el ID proporcionado por el cliente.
        $producto = Producto::find($solicitud->id);
        
        // Verifica si se encontró el registro del producto.
        if ($producto) {
            // Verifica si el producto tiene unidades asociadas a él.
            if ($producto->unidades()->exists()) {
                // Genera la respuesta HTTP para este caso.
                return response([
                    'message' => 'No se puede eliminar el producto mientras existan unidades asociadas a él.'
                ], 403);
            } else {
                // Verifica si el producto tiene una imagen.
                if ($producto->imagen_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $producto->imagen_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                }
                
                // Obtiene el registro de la marca del producto.
                $marca = Marca::find($producto->marca_id);
                
                // Elimina el registro del producto.
                $producto->delete();
                
                // Verifica si la marca no está asociada con otros productos.
                if (!$marca->productos()->exists()) {
                    // Elimina la marca.
                    $marca->delete();
                }
                
                // Genera la respuesta HTTP del resultado de la operación.
                return response([
                    'message' => 'Producto eliminado.'
                ], 200);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del producto.
            return response([
                'message' => 'Producto no encontrado.'
            ], 404);
        }
    }
}