<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarUsuarioHabilitado
{
    /**
     * Constata que el usuario esté 
     */
    public function handle(Request $solicitud, Closure $siguiente)
    {
        if (!auth()->user()->esta_habilitado) {
            return response([
                'message' => 'Cuenta inhabilitada.'
            ], 403);
        }
        
        return $siguiente($solicitud);
    }
}
