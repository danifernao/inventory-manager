<?php

namespace App\Http\Controllers;

use App\Models\Aplicacion;
use App\Models\Barrio;
use App\Models\Bodega;
use App\Models\BodegaProducto;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Fabricante;
use App\Models\Movimiento;
use App\Models\Municipio;
use App\Models\Notificacion;
use App\Models\Pais;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Proyecto;
use App\Models\Unidad;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Illuminate\Support\Facades\Password;


class AplicacionController extends Controller
{
    /**
     * Obtiene los datos generales de la aplicación para la vista principal.
     */
    public function index()
    {
        // Obtiene el único registro en donde se almacenan los datos generales de la aplicación.
        $aplicacion = Aplicacion::all()->first();
        
        // Retorna el registro.
        return view('index', [
            'aplicacion' => $aplicacion
        ]);
    }
    
    
    /**
     * Obtiene los datos de la aplicación vía API.
     */
    public function ver(Request $solicitud)
    {
        // Obtiene el único registro en donde se almacenan los datos de generales la aplicación.
        $aplicacion = Aplicacion::all()->first();
        
        // Retorna el registro.
        return response($aplicacion, 200);
    }
    
    
    /**
     * Edita los datos de la aplicación.
     * Retorna el registro actualizado.
     */    
    public function editar (Request $solicitud)
    {
        // Obtiene el único registro en donde se almacenan los datos generales de la aplicación.
        $aplicacion = Aplicacion::all()->first();
        
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'sometimes|string|max:500',
            'icono' => 'nullable|image:jpg,jpeg,png',
            'favicon' => 'nullable|image:jpg,jpeg,png',            
            'img_autenticacion' => 'nullable|image:jpg,jpeg,png',
            'img_inventario' => 'nullable|image:jpg,jpeg,png'
        ]);
        
        // Edita la instancia del registro con los campos proporcionados por el cliente.
        $aplicacion->fill($solicitud->all());
        
        // Define la variable que contendrá los errores encontrados en el proceso.
        $errores = array();
        
        // Verifica si se proporcionó el campo "icono".
        if ($solicitud->exists('icono')) {
            // Verifica si se proporcionó el archivo de una imagen.
            if ($solicitud->hasFile('icono')) {
                // Sube la imagen y obtiene su URL.
                $url = self::subirImagen('icono', $solicitud->icono);
                
                // Verifica si se obtuvo la URL.
                if ($url) {
                    // Verifica si el registro tiene una imagen; si la tiene, se procede a eliminarla.
                    if ($aplicacion->img_icono_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $aplicacion->img_icono_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                    }
                    
                    // Guarda la URL de la imagen en la instancia del registro.
                    $aplicacion->img_icono_url = $url;
                } else {
                    // Agrega el error al arreglo de errores.
                    array_push($errores, 'Icono no válido.');
                }
            } else {
                // Cuando se recibe el campo "logotipo" sin nada en él, el sistema lo interpreta
                // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                    
                // Verifica si el registro tiene una imagen.
                if ($aplicacion->img_icono_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $aplicacion->img_icono_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                    
                    // Elimina la URL del registro.
                    $aplicacion->img_icono_url = null;
                }
            }
        }
        
        // Verifica si se proporcionó el campo "favicon".
        if ($solicitud->exists('favicon')) {
            // Verifica si se proporcionó el archivo de una imagen.
            if ($solicitud->hasFile('favicon')) {
                // Sube la imagen y obtiene su URL.
                $url = self::subirImagen('favicon', $solicitud->favicon);
                
                // Verifica si se obtuvo la URL.
                if ($url) {
                    // Verifica si el registro tiene una imagen; si la tiene, se procede a eliminarla.
                    if ($aplicacion->img_favicon_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $aplicacion->img_favicon_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                    }
                    
                    // Guarda la URL de la imagen en la instancia del registro.
                    $aplicacion->img_favicon_url = $url;
                } else {
                    // Agrega el error al arreglo de errores.
                    array_push($errores, 'Favicón no válido.');
                }
            } else {
                // Cuando se recibe el campo "logotipo" sin nada en él, el sistema lo interpreta
                // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                    
                // Verifica si el registro tiene una imagen.
                if ($aplicacion->img_favicon_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $aplicacion->img_favicon_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                    
                    // Elimina la URL del registro.
                    $aplicacion->img_favicon_url = null;
                }
            }
        }
        
        // Verifica si se proporcionó el campo "img_autenticacion".
        if ($solicitud->exists('img_autenticacion')) {
            // Verifica si se proporcionó el archivo de una imagen.
            if ($solicitud->hasFile('img_autenticacion')) {
                // Sube la imagen y obtiene su URL.
                $url = self::subirImagen('img_autenticacion', $solicitud->img_autenticacion);
                
                // Verifica si se obtuvo la URL.
                if ($url) {
                    // Verifica si el registro tiene una imagen; si la tiene, se procede a eliminarla.
                    if ($aplicacion->img_autenticacion_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $aplicacion->img_autenticacion_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                    }
                    
                    // Guarda la URL de la imagen en la instancia del registro.
                    $aplicacion->img_autenticacion_url = $url;
                } else {
                    // Agrega el error al arreglo de errores.
                    array_push($errores, 'Imagen de la cabecera del formulario no válida.');
                }
            } else {
                // Cuando se recibe el campo "logotipo" sin nada en él, el sistema lo interpreta
                // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                    
                // Verifica si el registro del producto tiene una imagen.
                if ($aplicacion->img_autenticacion_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $aplicacion->img_autenticacion_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                    
                    // Elimina la URL del registro.
                    $aplicacion->img_autenticacion_url = null;
                }
            }
        }
        
        // Verifica si se proporcionó el campo "img_inventario".
        if ($solicitud->exists('img_inventario')) {
            // Verifica si se proporcionó el archivo de una imagen.
            if ($solicitud->hasFile('img_inventario')) {
                // Sube la imagen y obtiene su URL.
                $url = self::subirImagen('img_inventario', $solicitud->img_inventario);
                
                // Verifica si se obtuvo la URL.
                if ($url) {
                    // Verifica si el registro tiene una imagen;
                    // si la tiene, se procede a eliminarla.
                    if ($aplicacion->img_inventario_url) {
                        // Obtiene la ruta de la imagen.
                        // Para la gestión de los archivos en Laravel, se hace necesario el
                        // reemplazo del nombre del directorio "storage" a "public".
                        $ruta = str_replace('/storage/', '/public/', $aplicacion->img_inventario_url);
                        
                        // Elimina la imagen del almacenamiento local.
                        Storage::delete($ruta);
                    }
                    
                    // Guarda la URL de la imagen en la instancia del registro.
                    $aplicacion->img_inventario_url = $url;
                } else {
                    // Agrega el error al arreglo de errores.
                    array_push($errores, 'Imagen de la cabecera de la aplicación no válida.');
                }
            } else {
                // Cuando se recibe el campo "logotipo" sin nada en él, el sistema lo interpreta
                // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                    
                // Verifica si el registro tiene una imagen.
                if ($aplicacion->img_inventario_url) {
                    // Obtiene la ruta de la imagen.
                    // Para la gestión de los archivos en Laravel, se hace necesario el
                    // reemplazo del nombre del directorio "storage" a "public".
                    $ruta = str_replace('/storage/', '/public/', $aplicacion->img_inventario_url);
                    
                    // Elimina la imagen del almacenamiento local.
                    Storage::delete($ruta);
                    
                    // Elimina la URL del registro.
                    $aplicacion->img_inventario_url = null;
                }
            }
        }
        
        // Guarda los cambios en la base de datos.
        $aplicacion->save();
        
        // Actualiza la instancia "Aplicacion" con el respectivo registro de la base de datos.
        $aplicacion->refresh();
        
        // Verifica si no se encontraron errores.
        if (empty($errores)) {
            // Retorna el registro actualizado.
            return response($aplicacion, 200);
        } else {
            // Genera la respuesta HTTP con los errores encontrados.
            return response([
                'errors' => [ 'media' => $errores ],
                'message' => 'Archivo no válido'
            ], 422);
        }
    }
    
    
    /**
     * Guarda una imagen.
     * Retorna la URL de la imagen.
     */
     
    public function subirImagen ($nombre, $archivo)
    {
        // Verifica si el archivo proporcionado es válido.
        if ($archivo->isValid()) {
            // Genera la ruta para la imagen.
            $ruta = $archivo->hashName('public/aplicacion');
            
            // Define la variable que contendrá la imagen editada.
            $imagen = null;
            
            // Evalúa el nombre de la imagen.
            // Redimensiona la imagen según el caso.
            switch ($nombre) {
                case 'icono':
                    $imagen = Image::make($archivo)->resize(310, 310);
                case 'favicon':
                    $imagen = Image::make($archivo)->resize(192, 192);
                case 'img_autenticacion':
                    $imagen = Image::make($archivo)->widen(500);
                case 'img_inventario':
                    $imagen = Image::make($archivo)->widen(500);
            }
            
            // Guarda la imagen en el almacenamiento local.
            Storage::put($ruta, (string) $imagen->encode());
            
            // Retorna la URL de la imagen.
            return Storage::url($ruta);
        } else {
            return false;
        }
    }
    
    
    /**
     * Elimina todas las bodegas, proyectos, fabricantes, proveedores, productos, unidades y movimientos.
     */    
    public function eliminar (Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'contrasena' => 'required|string|max:255'
        ]);
        
        // Obtiene el registro del usuario autenticado.
        $solicitante = auth()->user();
        
        // Define la variable que identificará los intentos fallidos de confirmación de contraseña del
        // usuario autenticado.
        $intentosId = 'intentos-u' . $solicitante->id;
        
        // Verifica si el usuario autenticado no ha ingresado cinco veces su contraseña de manera incorrecta.
        if (RateLimiter::remaining($intentosId, 5)) {
            // Verifica si la contraseña del usuario autenticado es correcta.
            if (Hash::check($solicitud->contrasena, $solicitante->contrasena)) {
                // Reinicia el contador de intentos fallidos de confirmación de contraseña.
                RateLimiter::clear($intentosId);
                
                // Elimina todos los movimientos de las unidades.
                Movimiento::truncate();
                
                // Elimina todas las unidades.
                Unidad::truncate();
                
                // Elimina todos los productos.
                Producto::truncate();
                
                // Elimina todas las relaciones entre bodegas y productos.
                BodegaProducto::truncate();
                
                // Elimina todas las notificaciones.
                Notificacion::truncate();
                
                // Elimina todas las bodegas.
                Bodega::truncate();
                
                // Elimina todos los proyectos.
                Proyecto::truncate();
                
                // Elimina todos los fabricantes.
                Fabricante::truncate();
                
                // Elimina todos los proveedores.
                Proveedor::truncate();
                
                // Elimina todas las empresas.
                Empresa::truncate();
                
                // Elimina todos los barrios.
                Barrio::truncate();
                
                // Elimina todos los municipios.
                Municipio::truncate();
                
                // Elimina todos los departamentos.
                Departamento::truncate();
                
                // Elimina todos los países.
                Pais::truncate();
                
                // Elimina todas las imágenes del almacenamiento local.
                Storage::deleteDirectory('public/productos');
                Storage::deleteDirectory('public/empresas');
                
                // Retorna el resultado de la operación.
                return response([
                    'message' => 'Inventarios eliminados.'
                ], 200);
            } else {
                // Incrementa el contador de intentos fallidos de confirmación de contraseña.
                RateLimiter::hit($intentosId);
                
                // Genera la respuesta HTTP en caso de que la contraseña de confirmación sea incorrecta.
                return response([
                    'message' => 'La contraseña es incorrecta.'
                ], 401);
            }
        } else {
            // Inhabilita al usuario autenticado.
            $solicitante->esta_habilitado = false;
            
            // Registra la fecha de inhabilitación.
            $solicitante->fecha_inhabilitacion = Carbon::now();
            
            // Guarda los cambios en la base de datos.
            $solicitante->save();
            
            // Elimina todas las claves de acceso del usuario autenticado.
            $solicitante->tokens()->delete();
            
            // Genera la respuesta HTTP en el que se informa sobre el estado de la cuenta.
            return response([
                'message' => 'Cuenta inhabilitada temporalmente.'
            ], 403);
        }
    }
}