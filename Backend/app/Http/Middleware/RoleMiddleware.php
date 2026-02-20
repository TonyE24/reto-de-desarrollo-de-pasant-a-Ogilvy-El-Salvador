<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // este middleware revisa si el usuario tiene el rol requerido para acceder a la ruta
    // si no tiene el rol correcto simplemente le negamos el acceso con un 403
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // primero verifico que el usuario este autenticado
        if (!$request->user()) {
            return response()->json([
                'message' => 'No estas autenticado'
            ], 401);
        }

        // luego reviso si su rol coincide con el que requiere la ruta
        if ($request->user()->role !== $role) {
            return response()->json([
                'message' => 'No tienes permiso para acceder a esta seccion'
            ], 403); // 403 = forbidden, sabe quien eres pero no puedes entrar
        }

        // si todo esta bien dejo pasar la peticion
        return $next($request);
    }
}
