<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Tests de validación avanzada - Issue #32
 * Verifica que los FormRequests rechazan inputs inválidos correctamente
 */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    // helper: crea un usuario y devuelve su token para peticiones autenticadas
    private function tokenFor(User $user): string
    {
        return $user->createToken('test')->plainTextToken;
    }

    // ---------------------------------------------------------------
    // Tests de RegisterRequest
    // ---------------------------------------------------------------

    // test 1: registro rechaza nombre con caracteres especiales/HTML (XSS)
    public function test_register_sanitiza_nombre_con_html(): void
    {
        $respuesta = $this->postJson('/api/auth/register', [
            'name'                  => '<script>alert("xss")</script>',
            'email'                 => 'alex@example.com',
            'password'              => 'contrasena123',
            'password_confirmation' => 'contrasena123',
        ]);

        // el strip_tags lo convierte a string vacio/invalido => falla la regex del nombre
        $respuesta->assertStatus(422);
    }

    // test 2: registro rechaza email con formato incorrecto
    public function test_register_rechaza_email_con_formato_incorrecto(): void
    {
        $respuesta = $this->postJson('/api/auth/register', [
            'name'                  => 'Alexander Granados',
            'email'                 => 'no-es-email',
            'password'              => 'contrasena123',
            'password_confirmation' => 'contrasena123',
        ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // test 3: registro rechaza contraseñas menores a 8 caracteres
    public function test_register_rechaza_password_corta(): void
    {
        $respuesta = $this->postJson('/api/auth/register', [
            'name'                  => 'Alexander Granados',
            'email'                 => 'alex@example.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    // test 4: registro rechaza cuando las contraseñas no coinciden
    public function test_register_rechaza_passwords_que_no_coinciden(): void
    {
        $respuesta = $this->postJson('/api/auth/register', [
            'name'                  => 'Alexander Granados',
            'email'                 => 'alex@example.com',
            'password'              => 'contrasena123',
            'password_confirmation' => 'diferente456',
        ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    // ---------------------------------------------------------------
    // Tests de LoginRequest
    // ---------------------------------------------------------------

    // test 5: login rechaza si falta el email
    public function test_login_rechaza_si_falta_email(): void
    {
        $respuesta = $this->postJson('/api/auth/login', [
            'password' => 'contrasena123',
        ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    // test 6: login rechaza si falta la contraseña
    public function test_login_rechaza_si_falta_password(): void
    {
        $respuesta = $this->postJson('/api/auth/login', [
            'email' => 'alex@example.com',
        ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    // ---------------------------------------------------------------
    // Tests de StoreCompanyRequest
    // ---------------------------------------------------------------

    // test 7: crear empresa rechaza industria que no está en la lista permitida
    public function test_store_company_rechaza_industria_invalida(): void
    {
        $usuario = User::factory()->create();
        $token   = $this->tokenFor($usuario);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/companies', [
                'name'     => 'Mi Empresa',
                'industry' => 'IndustriaFalsa',  // no está en la lista
                'country'  => 'El Salvador',
                'region'   => 'San Salvador',
            ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['industry']);
    }

    // test 8: crear empresa rechaza más de 20 keywords
    public function test_store_company_rechaza_mas_de_20_keywords(): void
    {
        $usuario = User::factory()->create();
        $token   = $this->tokenFor($usuario);

        $keywords = array_fill(0, 21, 'keyword'); // 21 keywords = supera el límite

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/companies', [
                'name'     => 'Mi Empresa',
                'industry' => 'Tecnología',
                'country'  => 'El Salvador',
                'region'   => 'San Salvador',
                'keywords' => $keywords,
            ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['keywords']);
    }

    // test 9: crear empresa sanitiza tags HTML en el nombre (prevención XSS)
    public function test_store_company_sanitiza_html_en_nombre(): void
    {
        $usuario = User::factory()->create();
        $token   = $this->tokenFor($usuario);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/companies', [
                'name'     => '<b>Mi Empresa</b>',
                'industry' => 'Tecnología',
                'country'  => 'El Salvador',
                'region'   => 'San Salvador',
            ]);

        // el nombre guardado en BD no debe tener etiquetas HTML
        $this->assertDatabaseMissing('companies', ['name' => '<b>Mi Empresa</b>']);
        $this->assertDatabaseHas('companies', ['name' => 'Mi Empresa']);
    }

    // test 10: crear empresa requiere los campos obligatorios
    public function test_store_company_requiere_campos_obligatorios(): void
    {
        $usuario = User::factory()->create();
        $token   = $this->tokenFor($usuario);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/companies', []); // sin datos

        $respuesta->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'industry', 'country', 'region']);
    }

    // ---------------------------------------------------------------
    // Tests de UpdateCompanyRequest
    // ---------------------------------------------------------------

    // test 11: actualizar empresa acepta actualización parcial (solo un campo)
    public function test_update_company_acepta_actualizacion_parcial(): void
    {
        $usuario  = User::factory()->create();
        $token    = $this->tokenFor($usuario);
        $empresa  = $usuario->companies()->create([
            'name' => 'Empresa Original', 'industry' => 'Tecnología',
            'country' => 'El Salvador', 'region' => 'San Salvador',
        ]);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/companies/{$empresa->id}", [
                'region' => 'Santa Ana', // solo actualizamos la region
            ]);

        $respuesta->assertStatus(200);
        $this->assertDatabaseHas('companies', ['id' => $empresa->id, 'region' => 'Santa Ana']);
    }

    // test 12: actualizar empresa rechaza industria invalida
    public function test_update_company_rechaza_industria_invalida(): void
    {
        $usuario = User::factory()->create();
        $token   = $this->tokenFor($usuario);
        $empresa = $usuario->companies()->create([
            'name' => 'Mi Empresa', 'industry' => 'Tecnología',
            'country' => 'El Salvador', 'region' => 'San Salvador',
        ]);

        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/companies/{$empresa->id}", [
                'industry' => 'Industria Fantasma',
            ]);

        $respuesta->assertStatus(422)->assertJsonValidationErrors(['industry']);
    }
}
