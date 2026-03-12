<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * RequestLogMiddleware - Issue #34
 *
 * Middleware global que registra cada request HTTP que llega a la API.
 * Guarda: metodo, URL, usuario, IP, status code y tiempo de respuesta.
 * Los logs van al canal 'access' (storage/logs/access.log)
 */
class RequestLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // guardamos el tiempo de inicio para calcular la duracion al final
        $startTime = microtime(true);

        // dejamos pasar el request al controlador
        $response = $next($request);

        // calculamos cuanto tardo en milisegundos
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // construimos el contexto que queremos guardar en el log
        $context = [
            'method'      => $request->method(),
            'url'         => $request->fullUrl(),
            'ip'          => $request->ip(),
            'user_id'     => $request->user()?->id ?? 'guest',
            'status'      => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_agent'  => $request->userAgent(),
        ];

        // seleccionamos el nivel de log segun el status code de la respuesta
        // 5xx = errores del servidor → warning
        // 4xx = errores del cliente → info
        // 2xx/3xx = exito → info
        if ($response->getStatusCode() >= 500) {
            Log::channel('access')->warning('API Request - Server Error', $context);
        } elseif ($response->getStatusCode() >= 400) {
            Log::channel('access')->info('API Request - Client Error', $context);
        } else {
            Log::channel('access')->info('API Request - Success', $context);
        }

        return $response;
    }
}
