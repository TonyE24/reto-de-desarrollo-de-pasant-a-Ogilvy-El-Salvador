<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Tests de Rate Limiting - Issue #33
 * Verifica que los endpoints responden 429 al superar el límite
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    // limpiamos los limiters antes de cada test para que no interfieran entre si
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('auth|' . request()->ip());
    }

    // helper: devuelve token de un usuario para peticiones autenticadas
    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    // ---------------------------------------------------------------
    // Tests de throttle:auth (límite: 5 req/min)
    // ---------------------------------------------------------------

    // test 1: el login responde normalmente dentro del límite
    public function test_login_responde_200_dentro_del_limite(): void
    {
        User::factory()->create(['email' => 'alex@example.com', 'password' => bcrypt('pass12345')]);

        $respuesta = $this->postJson('/api/auth/login', [
            'email'    => 'alex@example.com',
            'password' => 'pass12345',
        ]);

        // dentro del límite debe responder normalmente (200 o 401, no 429)
        $this->assertNotEquals(429, $respuesta->status());
    }

    // test 2: el login devuelve 429 al superar el límite de 5 intentos
    public function test_login_devuelve_429_al_superar_limite(): void
    {
        // limpiamos el rate limiter para este test
        RateLimiter::clear('auth|' . $this->app->make('request')->ip());

        // hacemos 6 peticiones (una más que el límite de 5)
        for ($i = 0; $i < 6; $i++) {
            $respuesta = $this->postJson('/api/auth/login', [
                'email'    => 'intento@example.com',
                'password' => 'cualquierpass',
            ]);
        }

        // la ultima peticion debe ser rechazada con 429
        $respuesta->assertStatus(429);
    }

    // test 3: la respuesta 429 tiene el mensaje correcto en español
    public function test_respuesta_429_tiene_mensaje_en_espanol(): void
    {
        RateLimiter::clear('auth|' . $this->app->make('request')->ip());

        // superamos el límite
        for ($i = 0; $i <= 5; $i++) {
            $respuesta = $this->postJson('/api/auth/login', [
                'email'    => 'spam@example.com',
                'password' => 'contrasena',
            ]);
        }

        $respuesta->assertStatus(429);
        $respuesta->assertJson([
            'message' => 'Demasiados intentos. Por favor espera 1 minuto antes de intentarlo de nuevo.',
            'retry_after' => 60,
        ]);
    }

    // test 4: el registro también tiene rate limiting
    public function test_register_devuelve_429_al_superar_limite(): void
    {
        RateLimiter::clear('auth|' . $this->app->make('request')->ip());

        for ($i = 0; $i <= 5; $i++) {
            $respuesta = $this->postJson('/api/auth/register', [
                'name'                  => "Usuario {$i}",
                'email'                 => "user{$i}@example.com",
                'password'              => 'contrasena123',
                'password_confirmation' => 'contrasena123',
            ]);
        }

        $respuesta->assertStatus(429);
    }

    // ---------------------------------------------------------------
    // Tests de throttle:data (límite: 60 req/min)
    // ---------------------------------------------------------------

    // test 5: el endpoint de inteligencia responde normalmente cuando no hay spam
    public function test_intelligence_responde_dentro_del_limite(): void
    {
        $usuario = User::factory()->create();
        $empresa = $usuario->companies()->create([
            'name' => 'Test', 'industry' => 'Tecnología',
            'country' => 'El Salvador', 'region' => 'San Salvador',
        ]);
        $token = $this->tokenFor($usuario);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/intelligence/market?company_id=' . $empresa->id);

        // no debe dar 429 (solo 1 peticion)
        $this->assertNotEquals(429, $respuesta->status());
    }

    // ---------------------------------------------------------------
    // Tests de throttle:dashboard (límite: 30 req/min)
    // ---------------------------------------------------------------

    // test 6: el dashboard responde normalmente en la primera peticion
    public function test_dashboard_responde_dentro_del_limite(): void
    {
        $usuario = User::factory()->create();
        $empresa = $usuario->companies()->create([
            'name' => 'Test', 'industry' => 'Tecnología',
            'country' => 'El Salvador', 'region' => 'San Salvador',
        ]);
        $token = $this->tokenFor($usuario);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/dashboard?company_id=' . $empresa->id);

        // no debe dar 429
        $this->assertNotEquals(429, $respuesta->status());
    }
}
