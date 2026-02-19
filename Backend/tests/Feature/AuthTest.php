<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // esto limpia la base de datos despues de cada test para que no haya basura
    use RefreshDatabase;

    // test 1: ver si un usuario se puede registrar bien
    public function test_usuario_puede_registrarse(): void
    {
        // estos son los datos que mando al registro
        $datos = [
            'name'                  => 'Alexander Granados',
            'email'                 => 'alex@example.com',
            'password'              => 'alexito123',
            'password_confirmation' => 'alexito123',
        ];

        // mando la peticion al endpoint de registro
        $respuesta = $this->postJson('/api/auth/register', $datos);

        // reviso que me devuelva 201 y que tenga token y datos del user
        $respuesta
            ->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role'],
                'token',
            ]);

        // confirmo que el user si quedo guardado en la BD
        $this->assertDatabaseHas('users', [
            'email' => 'alex@example.com',
            'role'  => 'user',
        ]);
    }

    // test 2: que pasa si intento registrarme con un email que ya existe
    public function test_no_puede_registrarse_con_email_duplicado(): void
    {
        // creo un user con ese email primero
        User::factory()->create(['email' => 'alex@example.com']);

        // intento crear otro con el mismo email, no deberia dejarlo
        $respuesta = $this->postJson('/api/auth/register', [
            'name'                  => 'Alexander Granados',
            'email'                 => 'alex@example.com',
            'password'              => 'alexito123',
            'password_confirmation' => 'alexito123',
        ]);

        // si llega 422 es porque rechazo el email duplicado, bien
        $respuesta->assertStatus(422);
    }

    // test 3: probar que el login funciona con credenciales correctas
    public function test_usuario_puede_hacer_login(): void
    {
        // creo un user de prueba en la BD
        $usuario = User::factory()->create([
            'email'    => 'alex@example.com',
            'password' => bcrypt('alexito123'),
        ]);

        // intento hacer login con sus datos
        $respuesta = $this->postJson('/api/auth/login', [
            'email'    => 'alex@example.com',
            'password' => 'alexito123',
        ]);

        // si devuelve 200 y trae token, el login funciona
        $respuesta
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);
    }

    // test 4: que pasa si pongo la contrasena mal en el login
    public function test_no_puede_hacer_login_con_credenciales_incorrectas(): void
    {
        // creo el user
        User::factory()->create([
            'email'    => 'alex@example.com',
            'password' => bcrypt('alexito123'),
        ]);

        // intento login con contrasena incorrecta
        $respuesta = $this->postJson('/api/auth/login', [
            'email'    => 'alex@example.com',
            'password' => 'contrasena_mal',
        ]);

        // debe dar 401 porque no esta autorizado
        $respuesta->assertStatus(401);
    }

    // test 5: cerrar sesion con el token
    public function test_usuario_puede_hacer_logout(): void
    {
        // creo un user y genero su token
        $usuario = User::factory()->create();
        $token   = $usuario->createToken('auth_token')->plainTextToken;

        // mando la peticion de logout con el token en el header
        $respuesta = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        // si devuelve 200 el logout funciono
        $respuesta->assertStatus(200);

        // verifico que el token ya no existe en la BD
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
