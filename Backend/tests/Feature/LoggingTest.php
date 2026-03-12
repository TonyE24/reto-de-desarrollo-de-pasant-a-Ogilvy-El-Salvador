<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Tests de Logging y Monitoreo - Issue #34
 */
class LoggingTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Tests del middleware de accesos (RequestLogMiddleware)
    // ---------------------------------------------------------------

    // test 1: una peticion exitosa genera un log en el canal 'access'
    public function test_request_exitoso_genera_log_de_acceso(): void
    {
        // interceptamos el canal de log 'access' para verificar sin escribir archivos
        Log::shouldReceive('channel')
            ->with('access')
            ->once()
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                // verificamos que el log tiene la estructura correcta
                return str_contains($message, 'API Request')
                    && isset($context['method'])
                    && isset($context['url'])
                    && isset($context['status'])
                    && isset($context['duration_ms']);
            });

        // hacemos una peticion a un endpoint publico
        $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);
    }

    // test 2: el middleware registra el metodo HTTP correcto
    public function test_log_registra_metodo_http_correcto(): void
    {
        Log::shouldReceive('channel')->with('access')->once()->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['method'] === 'POST';
            });

        $this->postJson('/api/auth/login', [
            'email'    => 'x@x.com',
            'password' => 'cualquier',
        ]);
    }

    // test 3: el middleware registra el user_id del usuario autenticado
    public function test_log_registra_user_id_del_usuario_autenticado(): void
    {
        $usuario = User::factory()->create();
        $token   = $usuario->createToken('test')->plainTextToken;

        Log::shouldReceive('channel')->with('access')->once()->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($usuario) {
                return $context['user_id'] === $usuario->id;
            });

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/user');
    }

    // test 4: peticiones sin autenticar registran 'guest' como user_id
    public function test_log_registra_guest_para_peticiones_sin_token(): void
    {
        Log::shouldReceive('channel')->with('access')->once()->andReturnSelf();
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['user_id'] === 'guest';
            });

        $this->postJson('/api/auth/login', [
            'email' => 'nadie@x.com', 'password' => 'abc',
        ]);
    }

    // test 5: el middleware usa nivel 'warning' para respuestas 5xx
    public function test_middleware_usa_warning_para_respuestas_5xx(): void
    {
        // probamos el middleware directamente sin pasar por el router
        $middleware = new \App\Http\Middleware\RequestLogMiddleware();

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');

        // creamos una respuesta falsa con status 500
        $next = fn($req) => response()->json(['error' => 'fallo'], 500);

        Log::shouldReceive('channel')->with('access')->once()->andReturnSelf();
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $context['status'] === 500
                    && str_contains($message, 'Server Error');
            });

        $middleware->handle($request, $next);
    }

    // ---------------------------------------------------------------
    // Tests del logging de excepciones
    // ---------------------------------------------------------------

    // test 6: los errores de validacion NO se loguean en 'errors' (son esperados)
    public function test_errores_de_validacion_no_se_loguean(): void
    {
        // el canal 'errors' NO debe recibir ninguna llamada
        Log::shouldReceive('channel')->with('errors')->never();

        // permitimos el canal 'access' para el middleware
        Log::shouldReceive('channel')->with('access')->andReturnSelf();
        Log::shouldReceive('info')->andReturn(null);

        // un error 422 de validacion no debe disparar el log de errores
        $this->postJson('/api/auth/register', []); // sin datos = 422
    }
}
