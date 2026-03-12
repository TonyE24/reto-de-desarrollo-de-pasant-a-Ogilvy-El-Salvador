<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // alias para el middleware de roles
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Issue #34: registra todas las peticiones API en storage/logs/access.log
        // se agrega al grupo 'api' para que aplique solo a rutas de la API
        $middleware->appendToGroup('api', \App\Http\Middleware\RequestLogMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Issue #34: captura y loguea todas las excepciones no manejadas
        // en el canal 'errors' (storage/logs/errors.log)
        $exceptions->report(function (\Throwable $e) {
            // no logueamos errores de validacion (422) ni de autenticacion (401/403)
            // porque son esperados y ya estan controlados en los FormRequests
            $skipClasses = [
                \Illuminate\Validation\ValidationException::class,
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException::class,
            ];

            foreach ($skipClasses as $skipClass) {
                if ($e instanceof $skipClass) {
                    return false; // no reportar, Laravel ya maneja estas
                }
            }

            // logueamos la excepcion con su traza completa
            Log::channel('errors')->error($e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);
        });
    })->create();
