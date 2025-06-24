<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // Especifica los atributos cuyos valores pueden ser asignados de manera masiva.
    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_identificacion',
        'numero_identificacion',
        'genero',
        'correo',
        'es_administrador',
        'esta_habilitado'
    ];
    
    // Especifica los atributos sensibles que no estarán visibles.
    protected $hidden = [
        'contrasena',
        'remember_token'
    ];
    
    // Aplica formato a los atributos especificados.
    protected $casts = [
        'es_administrador' => 'boolean',
        'esta_habilitado' => 'boolean',
        'fecha_inhabilitacion' => 'datetime',
        'created_at' => 'date:d/m/Y'
    ];
    
    // Define la relación muchos a muchos de Bodega-Producto-Notificacion.
    // Se trae consigo las columnas 'leido', 'created_at' y 'updated_at' de la tabla intermedia.
    public function notificaciones()
    {
        return $this->belongsToMany(BodegaProducto::class, 'notificaciones')
                    ->withPivot('id', 'leido')
                    ->withTimestamps();
    }
    
    /**
     * Los campos "username" y "password" son definidos y gestionados por Laravel,
     * por lo que al cambiar sus nombres, hay que hacer los siguientes ajustes
     * para que el "framework" se siga comportando adecuadamente.
     */
    public function getEmailAttribute()
    {
        return $this->correo;
    }
    
    public function setEmailAttribute($valor)
    {
        return $this->attributes['correo'] = strtolower($valor);
    }
    
    public function getAuthPassword()
    {
        return $this->contrasena;
    }
    
    public function setPasswordAttribute($valor)
    {
        return $this->attributes['contrasena'] = bcrypt($valor);
    }
    
    /**
     * Los siguientes ajustes se realizan con el objetivo de agregar
     * datos a la tabla "personal_access_tokens", gestionadas por Laravel.
     */
    public function createToken(string $name, array $abilities = ['*'])
    {
        // Instancia componente Agent para la identificación del navegador, de la plataforma y del dispositivo del cliente.
        $agente = new Agent();
        
        // Determina el dispositivo del cliente.
        if ($agente->isDesktop()) {
            $dispositivo = 'Escritorio';
        } elseif ($agente->isPhone()) {
            $dispositivo = 'Teléfono';
        } elseif ($agente->isTablet()) {
            $dispositivo = 'Tableta';
        } else {
            $dispositivo = 'Otro';
        }
        
        // Inserta los atributos "ip", "navegador", "plataforma" y "dispositivo".
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'ip' => $_SERVER['REMOTE_ADDR'], // Obtiene la dirección IP del cliente.
            'navegador' => $agente->browser(), // Obtiene el navegador del cliente.
            'plataforma' => $agente->platform(), // Obtiene la plataforma del cliente.
            'dispositivo' => $dispositivo, // Define el dispositivo del cliente.
        ]);
        
        // Retorna una nueva clave de acceso.
        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }
}