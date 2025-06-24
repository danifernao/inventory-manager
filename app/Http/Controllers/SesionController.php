<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class SesionController extends Controller
{
    /**
     * Autentica a un usuario.
     * Retorna el registro del usuario y su clave de acceso.
     */
    public function autenticar(Request $solicitud)
    {
        // Valida los campos proporcionados por el cliente.
        // Para más información sobre las reglas de validación de Laravel,
        // visite este enlace: https://laravel.com/docs/9.x/validation#available-validation-rules
        $rules = [
            'correo' => 'required|string|email:rfc',
            'contrasena' => 'required|string'
        ];
        
        // Solo agrega la regla de reCAPTCHA si se ha proporcionado la clave secreta.
        if (config('recaptcha.api_secret_key')) {
            $rules['recaptcha'] = 'required|recaptcha';
        }

        $solicitud->validate($rules);

        // Obtiene el registro del usuario con el correo proporcionado por el cliente.
        $usuario = Usuario::where('correo', $solicitud->correo)->first();
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Define la variable que identificará los intentos de inicio de sesión.
            $intentosId = 'intentos-u' . $usuario->id;
            
            // Verifica si el usuario está inhabilitado.
            if (!$usuario->esta_habilitado) {
                // Verifica si no se ha registrado la fecha de inhabilitación del usuario.
                // Cuando esto sucede, se da por entendido que la acción fue realizada por un administrador,
                // ya que ese campo solo lo emplea el sistema para sus inhabilitaciones temporales.
                
                // Cuando la acción de inhabilitación proviene de un administrador, se da por entendido
                // que es de carácter permanente.
                if (is_null($usuario->fecha_inhabilitacion)) {
                    // Genera la respuesta HTTP en el que se informa sobre el estado de la cuenta.
                    return response([
                        'message' => 'Cuenta inhabilitada.'
                    ], 403);
                } else {
                    // Obtiene las horas transcurridas desde que inició la inhabilitación.
                    $horasTranscurridas = $usuario->fecha_inhabilitacion->diffInHours(Carbon::now());
                    
                    // Verifica si ya pasó más de 24 horas.
                    if ($horasTranscurridas >= 24) {
                        // Reinicia el contador de intentos fallidos de inicio de sesión.
                        RateLimiter::clear($intentosId);
                        
                        // Habilita al usuario.
                        $usuario->esta_habilitado = true;
                        
                        // Retira la fecha de inhabilitación.
                        $usuario->fecha_inhabilitacion = null;
                        
                        // Guarda los cambios.
                        $usuario->save();
                    } else {
                        // Genera la respuesta HTTP en el que se informa sobre el estado de la cuenta.
                        return response([
                            'message' => 'Cuenta inhabilitada temporalmente.'
                        ], 403);
                    }
                }
            }
            
            // Verifica que el usuario no lleve cinco itentos fallidos de inicio de sesión.
            if (RateLimiter::remaining($intentosId, 5)) {
                // Verifica si la contraseña del usuario autenticado es correcta.
                if (Hash::check($solicitud->contrasena, $usuario->contrasena)) {
                    // Reinicia el contador de intentos de inicio de sesión fallidos.
                    RateLimiter::clear($intentosId);
                    
                    // Genera la clave de acceso.
                    $clave_api = $usuario->createToken('clave_api')->plainTextToken;
                    
                    // Retorna el registro del usuario junto con la clave ade acceso.
                    return response([
                        'user' => $usuario,
                        'token' =>  $clave_api
                    ], 201);
                } else {
                    // Incrementa el contador de intentos fallidos de inicio de sesión.
                    RateLimiter::hit($intentosId);
                    
                    // Genera la respuesta HTTP en caso de que la contraseña sea incorrecta.
                    return response([
                        'message' => 'Correo o contraseña incorrecta.'
                    ], 401);
                }
            } else {
                // Inhabilita al usuario.
                $usuario->esta_habilitado = false;
                
                // Registra la fecha de inhabilitación.
                $usuario->fecha_inhabilitacion = Carbon::now();
                
                // Guarda los cambios en la base de datos.
                $usuario->save();
                
                // Elimina todas las claves de acceso del usuario.
                $usuario->tokens()->delete();
                
                // Genera la respuesta HTTP en el que se informa sobre el estado de la cuenta.
                return response([
                    'message' => 'Cuenta inhabilitada temporalmente.'
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
     * Retorna las claves de acceso registradas del usuario.
     */
    public function listar(Request $solicitud)
    {
        // Obtiene el registro del usuario con el ID proporcionado por el cliente.
        $usuario = Usuario::find($solicitud->id);
        
        // Verifica si se encontró al usuario.
        if ($usuario) {
            // Obtiene el registro del usuario autenticado.
            $solicitante = auth()->user();
            
            // Verifica si el usuario solicitado es el mismo que el usuario autenticado, en caso contrario,
            // verifica si el usuario autenticado es administrador y la cuenta solicitada no es de respaldo.
            // Las sesiones de las cuentas de respaldo no pueden ser consultadas por terceros.
            if ($usuario->id === $solicitante->id || ($solicitante->es_administrador && !$usuario->es_respaldo)) {
                // Retorna las sesiones del usuario solicitado, exceptuando, por supuesto, los atributos sensibles.
                return response(
                    $usuario->tokens->makeHidden([
                        'tokenable_type',
                        'tokenable_id',
                        'token',
                        'name',
                        'abilities'
                    ]), 200);
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
    
    
    /**
     * Elimina la clave de acceso solicitada, lo que cerraría la sesión especificada.
     * También se puede eliminar la sesión actual a través de este método.
     */
    public function eliminar(Request $solicitud)
    {
        // Verifica si se solicitó cerrar la sesión actual del usuario autenticado.
        if ($solicitud->route()->getName() === 'cerrar.actual') {
            // Cierra la sesión del usuario autenticado.
            auth()->user()->currentAccessToken()->delete();
            
            // Genera la respuesta HTTP del resultado del proceso.
            return response([ 'message' => 'Sesión cerrada.' ], 200);
        } else {
            // Obtiene el registro del usuario con el ID proporcionado por el cliente.
            $usuario = Usuario::find($solicitud->id);
            
            // Verifica si se encontró al usuario.
            if ($usuario) {
                // Obtiene el registro del usuario autenticado.
                $solicitante = auth()->user();
                
                // Verifica si el usuario solicitado es el mismo que el usuario autenticado, en caso contrario,
                // verifica si el usuario autenticado es administrador y la cuenta solicitada no es de respaldo.
                // Las sesiones de los usuarios de respaldo no pueden ser cerradas por terceros.
                if ($usuario->id === $solicitante->id || ($solicitante->es_administrador && !$usuario->es_respaldo)) {
                    // Verifica si se solicitó cerrar todas las sesiones del usuario solicitado.
                    if ($solicitud->route()->getName() === 'cerrar.todas') {
                        // Verifica si el usuario solicitado es el mismo que el usuario autenticado.
                        if ($usuario->id === $solicitante->id) {
                            // Recorre la colección de sesiones del usuario solicitado.
                            foreach ($usuario->tokens as $sesion) {
                                // Verifica que la sesión no sea la actual.
                                if ($sesion->id !== $solicitante->currentAccessToken()->id) {
                                    // Cierra la sesión.
                                    $sesion->delete();
                                }
                            }
                        } else {
                            // Cierra todas las sesiones del usuario solicitado.
                            $usuario->tokens()->delete();
                        }
                        
                        // Genera la respuesta HTTP del resultado del proceso.
                        return response([ 'message' => 'Sesiones cerradas.' ], 200);
                    } else {
                        // Cierra la sesión con el ID proporcionado por el cliente.
                        $usuario->tokens()->where('id', $solicitud->sid)->delete();
                    }
                    
                    // Genera la respuesta HTTP del resultado del proceso.
                    return response([
                        'message' => 'Sesión cerrada.'
                    ]);
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
}
