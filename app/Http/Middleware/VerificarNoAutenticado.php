<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class VerificarNoAutenticado
{
    /**
     * Constata que el usuario no haya iniciado sesión.
     */
    public function handle(Request $solicitud, Closure $siguiente)
    {
        if (Auth::guard('sanctum')->check()) {
            return response([
                'message' => 'Ya se ha iniciado sesión.'
            ], 403);
        }
        
        return $siguiente($solicitud);
    }
}