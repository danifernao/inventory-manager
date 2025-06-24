<?php

namespace App\Http\Controllers;

use App\Exports\UsuariosExport;
use App\Models\Usuario;
use App\Rules\Contrasena;
use App\Notifications\EstablecerContrasena;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;

class UsuarioController extends Controller
{
    /**
     * Registra un usuario.
     * Retorna el usuario recién registrado.
     */
    public function registrar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'contrasena_autenticacion' => 'required|string|max:255',
            'primer_nombre' => 'required|string|max:255',
            'segundo_nombre' => 'nullable|string|max:255',
            'primer_apellido' => 'nullable|string|required_with:segundo_apellido|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'correo' => 'required|string|email:rfc|unique:usuarios,correo|max:255',
            'tipo_identificacion' => 'nullable|string|in:cc,ce,ti,rc,di',
            'numero_identificacion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|in:m,h,n,u',
            'es_administrador' => 'nullable|boolean',
            'esta_habilitado' => 'nullable|boolean',
            'foto' => 'nullable|image:jpg,jpeg,png'
        ]);
        
        // Obtiene el registro del usuario autenticado.
        $solicitante = auth()->user();
        
        // Define la variable que identificará los intentos fallidos de confirmación de contraseña del usuario autenticado.
        $intentosId = 'intentos-u' . $solicitante->id;
        
        // Verifica si el usuario autenticado no ha ingresado cinco veces su contraseña de manera incorrecta.
        if (RateLimiter::remaining($intentosId, 5)) {
            // Verifica si la contraseña del usuario autenticado es correcta.
            if (Hash::check($solicitud->contrasena_autenticacion, $solicitante->contrasena)) {
                // Reinicia el contador de intentos fallidos de confirmación de contraseña.
                RateLimiter::clear($intentosId);
                
                // Inicia una nueva instancia del modelo Usuario.
                $usuario = new Usuario;
                
                // Rellena los atributos de la instancia recién creada con los campos proporcionados por el cliente.
                $usuario->fill($solicitud->all());
                
                // Verifica si se proporcionó el archivo de una imagen.
                if ($solicitud->hasFile('foto')) {
                    // Verifica si el archivo proporcionado es válido.
                    if ($solicitud->file('foto')->isValid()) {
                        // Obtiene el archivo.
                        $archivo = $solicitud->file('foto');
                        
                        // Genera la ruta para la imagen.
                        $ruta = $archivo->hashName('public/usuarios');
                        
                        // Redimensiona la imagen.
                        $imagen = Image::make($archivo)->widen(160);
                        
                        // Guarda la imagen en el almacenamiento local.
                        Storage::put($ruta, (string) $imagen->encode());
                        
                        // Guarda la URL de la imagen en la instancia Usuario.
                        $usuario->foto_perfil_url = Storage::url($ruta);
                    } else {
                        // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                        return response([
                            'message' => 'Archivo no válido.'
                        ], 422);
                    }
                }
                
                // Genera una contraseña aleatoria cifrada.
                $usuario->contrasena = Hash::make(Str::random(10));
                
                // Inserta el registro en la base de datos.
                $usuario->save();
                
                // Actualiza la instancia Usuario con el respectivo registro de la base de datos.
                $usuario->refresh();
                
                // Genera una clave para iniciar el proceso de establecimiento de una
                // nueva contraseña por parte del usuario recién registrado.
                $clave = Password::getRepository()->create($usuario);
                
                // Envía un correo electrónico al usuario recién registrado para establecer la contraseña.
                $usuario->notify(new EstablecerContrasena($usuario->correo, $clave));
                
                // Retorna el usuario recién registrado.
                return response($usuario, 200);
            } else {
                // Incrementa el contador de intentos fallidos de confirmación de contraseña.
                RateLimiter::hit($intentosId);
                
                // Genera la respuesta HTTP en caso de que la contraseña de confirmación sea incorrecta.
                return response([
                    'message' => 'La contraseña para guardar los cambios es incorrecta.'
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
    
    
    /**
     * Lista los usuarios registrados.
     */
    public function listar(Request $solicitud)
    {
        // Verifica si se solcitó exportar la lista de todos los usuarios.
        if ($solicitud->exportar) {
            // Retorna el archivo generado.
            return Excel::download(new UsuariosExport, 'usuarios.xlsx');
        } else {
            // Valida los campos proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'buscar' => 'sometimes|string',
                'ordenar_por' => 'sometimes|string|in:nombres,apellidos,identificacion,genero,correo,es_administrador,esta_habilitado,created_at',
                'ordenar_en' => 'sometimes|string|in:asc,desc',
                'por_pagina' => 'sometimes|integer|between:1,300'
            ]);
            
            // Define los valores por defecto de los parámetros de la consulta SQL.
            $ordenar_por = $solicitud->ordenar_por ?: 'nombres'; // Nombre de la columna para la cláusula ORDER BY.
            $ordenar_en = $solicitud->ordenar_en ?: 'asc'; // Orden para la cláusula ORDER BY, puede ser ASC o DESC.
            $por_pagina = $solicitud->por_pagina ?: 20; // Valor para la cláusula LIMIT.
            
            // Prepara la consulta para obtener los usuarios, excluyendo al usuario autenticado y a los usuarios
            // registrados por defecto (cuentas de respaldo).
            $consulta = Usuario::where('id', '!=', auth()->user()->id)->where('es_respaldo', 0);
            
            // Verifica si se solicitó la búsqueda de una cadena de texto.
            if ($solicitud->filled('buscar')) {
                // Convierte la cadena en un arreglo, separando cada término.
                $terminos = explode(' ', $solicitud->buscar);
                
                // Define las columnas en las que se realizará la búsqueda.
                $columnas = ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido', 'numero_identificacion', 'correo'];
                
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
            
            // Ordena los registros de la consulta.
            switch ($ordenar_por) {
                case 'nombres':
                    // Ordena la consulta por ambos nombres.
                    $consulta = $consulta->orderBy('primer_nombre', $ordenar_en)->orderBy('segundo_nombre', $ordenar_en);
                    break;
                case 'apellidos':
                    // Ordena la consulta por ambos pallidos.
                    $consulta = $consulta->orderBy('primer_apellido', $ordenar_en)->orderBy('segundo_apellido', $ordenar_en);
                    break;
                case 'identificacion':
                    // Ordena la consulta por el tipo y número de identificación.
                    $consulta = $consulta->orderBy('tipo_identificacion', $ordenar_en)->orderBy('numero_identificacion', $ordenar_en);
                    break;
                case 'es_administrador':
                case 'esta_habilitado':
                    // Invierte el orden previamente definido.
                    $ordenar_en =  $ordenar_en === 'asc' ? 'desc' : 'asc';
                default:
                    // Ordena la consulta según lo previamente definido.
                    $consulta = $consulta->orderBy($ordenar_por, $ordenar_en);
                    break;
            }
            
            // Pagina la consulta.
            $consulta = $consulta->paginate($por_pagina)->withQueryString()->onEachSide(3);
            
            // Retorna el resultado de la consulta.
            return response($consulta, 200);
        }
    }
    
    
    /**
     * Obtiene el registro de un usuario.
     */
    public function ver(Request $solicitud)
    {
        // Obtiene el registro del usuario autenticado.
        $solicitante = auth()->user();
        
        // Verifica si se solicitó el registro del usuario autenticado.
        if ($solicitud->route()->getName() === 'autenticado') {
            // Retorna el usuario autenticado.
            return response($solicitante, 200);
        } else {
            // Obtiene el registro del usuario con el ID proporcionado por el cliente.
            $usuario = Usuario::find($solicitud->id);
            
            // Verifica si se encontró al usuario.
            if ($usuario) {
                // Verifica si el usuario solicitado es el mismo que el usuario autentcado; en caso de no serlo
                // verifica si el usuario autenticado es administrador y la cuenta solicitada no es de respaldo.
                if ($usuario->id === $solicitante->id || ($solicitante->es_administrador && !$usuario->es_respaldo)) {
                    // Retorna el registro del usuario solicitado.
                    return response($usuario, 200);
                } else {
                    // Genera la respuesta HTTP en caso de que el usuario autenticado no tenga los permisos suficientes.
                    return response([
                        'message' => 'Permisos insuficientes.'
                    ], 403);
                }
            } else {
                // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del usuario.
                return response([
                    'message' => 'Usuario no encontrado.'
                ], 404);
            }
        }
    }
    
    
    /**
     * Edita el registro de un usuario.
     * Retorna el registro actualizado.
     */
    public function editar(Request $solicitud)
    {
        // Obtiene el registro del usuario con el ID proporcionado por el cliente.
        $usuario = Usuario::find($solicitud->id);
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Obtiene el registro del usuario autenticado.
            $solicitante = auth()->user();
            
            // Verifica si el usuario solicitado es el mismo que el usuario autentcado; en caso de no serlo
            // verifica si el usuario autenticado es administrador y la cuenta solicitada no es de respaldo.
            if ($usuario->id === $solicitante->id || ($solicitante->es_administrador && !$usuario->es_respaldo)) {
                // Verifica si el usuario autenticado es administrador.
                // Solo el administrador puede editar la información del usuario, a excepción de la contraseña.
                if ($solicitante->es_administrador) {
                    // Valida los parámetros proporcionados por el cliente.
                    // Para más información sobre las reglas de validación de Laravel,
                    // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
                    $solicitud->validate([
                        'contrasena_autenticacion' => 'required|string|max:255',
                        'primer_nombre' => 'required|string|max:255',
                        'segundo_nombre' => 'nullable|string|max:255',
                        'primer_apellido' => 'nullable|string|required_with:segundo_apellido|max:255',
                        'segundo_apellido' => 'nullable|string|max:255',
                        'genero' => 'nullable|string|in:m,h,n,u',
                        'correo' => [ 'required', 'string', 'email:rfc', 'unique:usuarios,correo,'. $usuario->id .',id', 'max:255' ],
                        'contrasena' => [ 'nullable', 'string', new Contrasena, 'confirmed', 'max:72' ],
                        'tipo_identificacion' => 'nullable|string|in:cc,ce,ti,rc,di',
                        'numero_identificacion' => 'nullable|string|max:255',
                        'es_administrador' => 'nullable|boolean',
                        'esta_habilitado' => 'nullable|boolean',
                        'foto' => 'nullable|image:jpg,jpeg,png'
                    ]);
                } else {
                    // Valida los parámetros proporcionados por el cliente.
                    // Para más información sobre las reglas de validación de Laravel,
                    // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
                    $solicitud->validate([
                        'contrasena_autenticacion' => 'required|string|max:255',
                        'contrasena' => [ 'nullable', 'string', new Contrasena, 'confirmed', 'max:255' ]
                    ]);
                }
                
                // Define la variable que identificará los intentos fallidos de confirmación de contraseña del usuario autenticado.
                $intentosId = 'intentos-u' . $solicitante->id;
                
                // Verifica si el usuario autenticado no ha ingresado cinco veces su contraseña de manera incorrecta.
                if (RateLimiter::remaining($intentosId, 5)) {
                    // Verifica si la contraseña del usuario autenticado es correcta.
                    if (Hash::check($solicitud->contrasena_autenticacion, $solicitante->contrasena)) {
                        // Reinicia el contador de intentos fallidos de confirmación de contraseña.
                        RateLimiter::clear($intentosId);
                        
                        // Verifica si el usuario autenticado es administrador.
                        // Solo el administrador puede editar la información del usuario, a excepción de la contraseña.
                        if ($solicitante->es_administrador) {
                            // Rellena los atributos del usuario con los campos proporcionados por el cliente.
                            $usuario->fill($solicitud->except(['es_administrador', 'esta_habilitado']));
                            
                            // Verifica si el usuario solicitado no es el mismo que el usuario autenticado.
                            // El usuario administrador autenticado no puede cambiarse sus propios permisos.
                            if ($usuario->id !== $solicitante->id) {
                                // Verifica si el cliente proporcionó el tipo de usuario (administrador u operador).
                                // Verifica si hubo un cambio, comparando el valor porporcionado con el de la base de datos.
                                if ($solicitud->filled('es_administrador') && $usuario->es_administrador !== boolval($solicitud->es_administrador)) {
                                    // Cambia el tipo de usuario.
                                    $usuario->es_administrador = $solicitud->es_administrador;
                                }
                                
                                // Verifica si el cliente proporcionó el estado del usuario (habilitado o inhabilitado).
                                // Verifica si hubo un cambio, comparando el valor porporcionado con el de la base de datos.
                                if ($solicitud->filled('esta_habilitado') && $usuario->esta_habilitado !== boolval($solicitud->esta_habilitado)) {
                                    // Cambia el estado del usuario.
                                    $usuario->esta_habilitado = $solicitud->esta_habilitado;
                                    
                                    // Retira la fecha de inhabilitación.
                                    // Este campo solo lo utiliza el sistema al inhabilitar al usuario de manera preventiva,
                                    // dado que sus inhabilitaciones no son de carácter permanente (duran 24 horas).
                                    $usuario->fecha_inhabilitacion = null;
                                }
                            }
                            
                            // Verifica si se proporcionó el campo "foto".
                            if ($solicitud->exists('foto')) {
                                // Verifica si se proporcionó el archivo de una imagen.
                                if ($solicitud->hasFile('foto')) {
                                    // Verifica si el archivo proporcionado es válido.
                                    if ($solicitud->file('foto')->isValid()) {
                                        // Verifica si el registro de la empresa tiene una imagen;
                                        // si la tiene, se procede a eliminarla.
                                        if ($usuario->foto_perfil_url) {
                                            // Obtiene la ruta de la imagen.
                                            // Para la gestión de los archivos en Laravel, se hace necesario el
                                            // reemplazo del nombre del directorio "storage" a "public".
                                            $ruta = str_replace('/storage/', '/public/', $usuario->foto_perfil_url);
                                            
                                            // Elimina la imagen del almacenamiento local.
                                            Storage::delete($ruta);
                                        }
                                        
                                        // Obtiene el archivo.
                                        $archivo = $solicitud->file('foto');
                                        
                                        // Genera la ruta para la imagen.
                                        $ruta = $archivo->hashName('public/usuarios');
                                        
                                        // Redimensiona la imagen.
                                        $imagen = Image::make($archivo)->widen(160);
                                        
                                        // Guarda la imagen en el almacenamiento local.
                                        Storage::put($ruta, (string) $imagen->encode());
                                        
                                        // Guarda la URL de la imagen en la instancia Usuario.
                                        $usuario->foto_perfil_url = Storage::url($ruta);
                                    } else {
                                        // Genera la respuesta HTTP en caso de que el archivo no sea válido.
                                        return response([
                                            'message' => 'Archivo no válido.'
                                        ], 422);
                                    }
                                } else {
                                    // Cuando se recibe el campo "foto" sin nada en él, el sistema lo interpreta
                                    // como una solicitud de eliminación de la imagen que se tenga actualmente registrada.
                                    
                                    // Verifica si el registro del fabricante tiene una imagen.
                                    if ($usuario->foto_perfil_url) {
                                        // Obtiene la ruta de la imagen.
                                        // Para la gestión de los archivos en Laravel, se hace necesario el
                                        // reemplazo del nombre del directorio "storage" a "public".
                                        $ruta = str_replace('/storage/', '/public/', $usuario->foto_perfil_url);
                                        
                                        // Elimina la imagen del almacenamiento local.
                                        Storage::delete($ruta);
                                        
                                        // Elimina la URL del registro de la empresa.
                                        $usuario->foto_perfil_url = null;
                                    }
                                }
                            }
                        }
                        
                        // Verifica si el usuario autenticado proporcionó una nueva contraseña.
                        // El cambio solo tiene en cuenta si el usuario solicitado es el mismo que el usuario autenticado.
                        if ($solicitud->filled('contrasena') && $usuario->id === $solicitante->id) {
                            // Cifra la nueva contraseña y la guarda.
                            $usuario->contrasena = Hash::make($solicitud->contrasena);
                        }
                        
                        // Guarda los cambios en la base de datos.
                        $usuario->save();
                        
                        // Actualiza la instancia Usuario con el respectivo registro de la base de datos.
                        $usuario->refresh();
                        
                        // Retorna el registro del usuario solicitado.
                        return response($usuario, 200);
                    } else {
                        // Incrementa el contador de intentos fallidos de confirmación de contraseña.
                        RateLimiter::hit($intentosId);
                        
                        // Genera la respuesta HTTP en caso de que la contraseña de confirmación sea incorrecta.
                        return response([
                            'message' => 'La contraseña para guardar los cambios es incorrecta.'
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
            } else {
                // Genera la respuesta HTTP en caso de que el usuario autenticado no tenga los permisos suficientes.
                return response([
                    'message' => 'No tienes suficientes permisos para realizar esta acción.'
                ], 403);
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del usuario.
            return response([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Envía un correo electrónico al usuario para que cambie la contraseña.
     */
    public function restablecerContrasena(Request $solicitud)
    {
        // Define la variable que contendrá el registro del usuario.
        $usuario = null;
        
        // Obtiene el registro del usuario autenticado.
        $solicitante = auth()->user();
        
        // Verifica si la solicitud fue iniciada por un administrador.
        if ($solicitante && $solicitante->es_administrador) {
            // Obtiene el registro del usuario con el ID proporcionado por el cliente.
            $usuario = Usuario::find($solicitud->id);
        } else {
            // Valida los parámetros proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $rules = [
                'correo' => 'required|string|email:rfc'
            ];

            // Solo agrega la regla de reCAPTCHA si se ha proporcionado la clave secreta.
            if (config('recaptcha.api_secret_key')) {
                $rules['recaptcha'] = 'required|recaptcha';
            }
            
            $solicitud->validate($rules);
            
            // Obtiene el registro el usuario con el correo proporcionado por el cliente.
            $usuario = Usuario::where('correo', $solicitud->correo)->first();
        }
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Verifica si el usuario está habilitado, en caso contrario,
            // verifica si la solicitud fue iniciada por un administrador.
            if ($usuario->esta_habilitado || ($solicitante && $solicitante->es_administrador)) {
                // Envía un correo al usuario para el restablecimiento de la contraseña.
                $resultado = Password::sendResetLink([ 'correo' => $usuario->correo ]);
                
                // Verifica si el mensaje se pudo enviar.
                if ($resultado === Password::RESET_LINK_SENT) {
                    // Verifica si la solicitud fue iniciada por un administrador.
                    // Genera la respuesta HTTP del resultado del proceso.
                    if ($solicitante && $solicitante->es_administrador) {
                        return response([
                            'message' => 'Correo de restablecimiento enviado.'
                        ], 200);
                    } else {
                        return response([
                            'message' => 'Se ha enviado un mensaje a su correo con las indicaciones a seguir.'
                        ], 200);
                    }
                } else {
                    // Genera la respuesta HTTP en caso de que el proceso de envío del correo haya fallado.
                    return response([
                        'message' => __($resultado)
                    ], 500);
                }
            } else {
                // Verifica si el usuario no está habilitado.
                if (!$usuario->esta_habilitado) {
                    // Verifica si no se ha registrado la fecha de inhabilitación del usuario.
                    // Cuando esto sucede, se da por entendido que la acción fue realizada por un administrador,
                    // ya que ese campo solo lo emplea el sistema para sus inhabilitaciones temporales.
                    
                    // Cuando la acción de inhabilitación proviene de un administrador, se da por entendido
                    // que es de carácter permanente.
                    
                    // Genera la respuesta HTTP en el que se informa sobre el estado de la cuenta.
                    if (is_null($usuario->fecha_inhabilitacion)) {
                        return response([
                            'message' => 'Cuenta inhabilitada.'
                        ], 403);
                    } else {
                        return response([
                            'message' => 'Cuenta inhabilitada temporalmente.'
                        ], 403);
                    }
                } else {
                    // Genera la respuesta HTTP en caso de que el usuario autenticado no tenga los permisos suficientes.
                    return response([
                        'message' => 'No tienes suficientes permisos para realizar esta acción.'
                    ], 403);
                }
            }
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del usuario.
            return response([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }
    
    /**
     * Cambia la contraseña del usuario.
     */
    public function cambiarContrasena(Request $solicitud)
    {
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'email' => 'required|string|email:rfc',
            'contrasena' => [ 'required', 'string', new Contrasena, 'confirmed', 'max:72' ]
        ]);
        
        // Valida y cambia la contraseña.
        $resultado = Password::reset([
                'correo' => $solicitud->email,
                'password' => $solicitud->contrasena,
                'token' => $solicitud->token
            ],
            function ($usuario, $contrasena) {
                $usuario->forceFill([ 'contrasena' => Hash::make($contrasena) ])->setRememberToken(Str::random(60));
                $usuario->save();
                event(new PasswordReset($usuario));
            }
        );
        
        // Verifica si la contraseña se cambió.
        if ($resultado === Password::PASSWORD_RESET) {
            // Genera la respuesta HTTP en el que se informa sobre el cambio de la contraseña.
            return response([
                'message' => 'Contraseña cambiada.'
            ], 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya podido realizar el cambio de contraseña.
            return response([
                'message' => __($resultado)
            ], 400);
        }
    }
    
    
    /**
     * Otorga permisos a un usuario.
     */
    public function autorizar(Request $solicitud)
    {
        // Obtiene el registro del usuario con el ID proporcionado por el cliente.
        $usuario = Usuario::find($solicitud->id);
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Valida los parámetros proporcionados por el cliente.
            // Para más información sobre las reglas de validación de Laravel,
            // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
            $solicitud->validate([
                'accion' => 'required|string|in:administrador,operador,habilitar,inhabilitar'
            ]);
            
            // Verifica si el usuario fue registrado por defecto en la implantación del SI (respaldo).
            // Los permisos de este tipo de usuarios no se pueden editar.
            if ($usuario->es_respaldo) {
                // General la respuesta HTTP en el que se informa sobre la ausencia de permisos.
                return response([
                    'message' => 'No tienes suficientes permisos para realizar esta acción.'
                ], 403);
            }
            
            // Verifica si el usuario solicitado es el mismo que el usuario autenticado.
            // Un usuario no puede cambiarse sus propios permisos.
            if ($usuario->id === auth()->user()->id) {
                // General la respuesta HTTP en el que se informa sobre la ausencia de permisos.
                return response([
                    'message' => 'No puedes realizar esta acción sobre ti mismo.'
                ], 403);
            }
            
            // Valida la acción solicitada por el cliente.
            // Edita los atributos correspondientes del usuario para cada caso.
            switch ($solicitud->accion) {
                case 'administrador':
                    $usuario->es_administrador = 1;
                    break;
                case 'operador':
                    $usuario->es_administrador = 0;
                    break;
                case 'habilitar':
                    $usuario->esta_habilitado = 1;
                    $usuario->fecha_inhabilitacion = null;
                    break;
                case 'inhabilitar':
                    $usuario->esta_habilitado = 0;
                    $usuario->fecha_inhabilitacion = null;
                    break;
            }
            
            // Guarda los cambios en la base de datos.
            $usuario->save();
            
            // General la respuesta HTTP del resultado del proceso.
            return response([
                'message' => 'Permisos cambiados.'
            ], 200);
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del usuario.
            return response([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }
    
    
    /**
     * Elimina el registro de un usuario.
     */
    public function eliminar(Request $solicitud)
    {
        // Valida los parámetros proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $solicitud->validate([
            'contrasena' => 'required|string|max:255'
        ]);
        
        // Obtiene el registro del usuario con el ID proporcionado por el cliente.
        $usuario = Usuario::find($solicitud->id);
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Verifica si el usuario fue registrado por defecto en la implantación del SI (respaldo).
            // Los usuarios de este tipo no se pueden eliminar.
            if ($usuario->es_respaldo) {
                // General la respuesta HTTP en el que se informa sobre la ausencia de permisos.
                return response([
                    'message' => 'No tienes suficientes permisos para realizar esta acción.'
                ], 403);
            } else {
                // Obtiene el registro del usuario autenticado.
                $solicitante = auth()->user();
                
                // Verifica si el usuario solicitado es administrador.
                // Los usuarios administradores no se pueden eliminar.
                if ($usuario->es_administrador) {
                    // General la respuesta HTTP en el que se informa sobre la ausencia de permisos.
                    return response([
                        'message' => 'No puedes realizar esta acción sobre ti mismo u otro administrador.'
                    ], 403);
                } else {
                    // Define la variable que identificará los intentos fallidos de confirmación de contraseña del usuario autenticado.
                    $intentosId = 'intentos-u' . $solicitante->id;
                    
                    // Verifica si el usuario autenticado no ha ingresado cinco veces su contraseña de manera incorrecta.
                    if (RateLimiter::remaining($intentosId, 5)) {
                        // Verifica si la contraseña del usuario autenticado es correcta.
                        if (Hash::check($solicitud->contrasena, $solicitante->contrasena)) {
                            // Reinicia el contador de intentos fallidos de confirmación de contraseña.
                            RateLimiter::hit($intentosId);
                            
                            // Verifica si el registro del usuario tiene una foto.
                            if ($usuario->foto_perfil_url) {
                                // Obtiene la ruta de la imagen.
                                // Para la gestión de los archivos en Laravel, se hace necesario el
                                // reemplazo del nombre del directorio "storage" a "public".
                                $ruta = str_replace('/storage/', '/public/', $usuario->foto_perfil_url);
                                
                                // Elimina la imagen del almacenamiento local.
                                Storage::delete($ruta);
                            }
                            
                            // Elimina las claves de acceso del usuario.
                            $usuario->tokens()->delete();
                            
                            // Elimina el registro del usuario.
                            $usuario->delete();
                            
                            // Genera la respuesta HTTP del resultado de la operación.
                            return response([
                                'message' => 'Usuario eliminado.'
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
        } else {
            // Genera la respuesta HTTP en caso de que no se haya encontrado el registro del usuario.
            return response([
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
    }
}