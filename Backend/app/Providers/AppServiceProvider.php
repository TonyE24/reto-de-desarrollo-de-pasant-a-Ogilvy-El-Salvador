<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * Aqui definimos los rate limiters del proyecto (Issue #33)
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Define los tres niveles de rate limiting según el tipo de endpoint.
     *
     * - auth:      5 intentos por minuto por IP (protege contra fuerza bruta)
     * - data:     60 requests por minuto por usuario (endpoints de inteligencia)
     * - dashboard: 30 requests por minuto por usuario (endpoint consolidado)
     */
    protected function configureRateLimiting(): void
    {
        // endpoints de autenticacion: 5 peticiones por minuto por IP
        // es estricto para proteger contra ataques de fuerza bruta en login/register
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos. Por favor espera 1 minuto antes de intentarlo de nuevo.',
                        'retry_after' => 60,
                    ], 429);
                });
        });

        // endpoints de datos de inteligencia: 60 por minuto por usuario autenticado
        // si no esta autenticado usamos la IP como fallback
        RateLimiter::for('data', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Has alcanzado el limite de consultas. Intenta de nuevo en 1 minuto.',
                        'retry_after' => 60,
                    ], 429);
                });
        });

        // endpoint del dashboard consolidado: 30 por minuto por usuario
        // es mas bajo porque consolida multiples fuentes de datos
        RateLimiter::for('dashboard', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Limite del dashboard alcanzado. Intenta de nuevo en 1 minuto.',
                        'retry_after' => 60,
                    ], 429);
                });
        });
    }
}
