<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class VerificarUsuarioAdmin
{
    /**
     * Constata que el usuario sea el administrador.
     */
    public function handle(Request $solicitud, Closure $siguiente)
    {
        if (!auth()->user()->es_administrador) {
            return response([
                'message' => 'No tienes suficientes permisos para realizar esta acción.'
            ], 403);
        }
        
        return $siguiente($solicitud);
    }
}
