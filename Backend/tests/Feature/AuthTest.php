<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
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

    // ---------------------------------------------------------------
    // Tests del Issue #10: Recuperación y Reset de Contraseña
    // ---------------------------------------------------------------

    // test 6: el endpoint forgot-password responde 200 cuando el email existe
    public function test_forgot_password_envia_link_con_email_valido(): void
    {
        // interceptamos las notificaciones para no mandar emails reales en tests
        Notification::fake();

        // creo un user con email conocido
        $usuario = User::factory()->create(['email' => 'alex@example.com']);

        // llamo al endpoint de forgot password
        $respuesta = $this->postJson('/api/auth/forgot-password', [
            'email' => 'alex@example.com',
        ]);

        // debe devolver 200 y un mensaje de exito
        $respuesta
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Te enviamos un link para restablecer tu contrasena']);

        // verifico que si se envio la notificacion de reset al usuario
        Notification::assertSentTo($usuario, ResetPasswordNotification::class);
    }

    // test 7: forgot-password devuelve 404 si el email no esta registrado
    public function test_forgot_password_devuelve_404_con_email_no_registrado(): void
    {
        $respuesta = $this->postJson('/api/auth/forgot-password', [
            'email' => 'noexiste@example.com',
        ]);

        // si el email no existe debe dar 404
        $respuesta->assertStatus(404);
    }

    // test 8: forgot-password devuelve 422 si el email tiene formato incorrecto
    public function test_forgot_password_valida_formato_de_email(): void
    {
        $respuesta = $this->postJson('/api/auth/forgot-password', [
            'email' => 'esto-no-es-un-email',
        ]);

        // validacion de formato falla, Laravel devuelve 422
        $respuesta->assertStatus(422);
    }

    // test 9: reset-password cambia la contrasena con un token real valido
    public function test_reset_password_cambia_contrasena_con_token_valido(): void
    {
        // creo el user con contraseña conocida
        $usuario = User::factory()->create([
            'email'    => 'alex@example.com',
            'password' => Hash::make('contrasena_vieja'),
        ]);

        // genero un token real de reset usando el broker de Laravel
        $token = Password::createToken($usuario);

        // llamo al endpoint de reset con el token y la nueva contraseña
        $respuesta = $this->postJson('/api/auth/reset-password', [
            'token'                 => $token,
            'email'                 => 'alex@example.com',
            'password'              => 'nueva_contrasena_123',
            'password_confirmation' => 'nueva_contrasena_123',
        ]);

        // debe devolver 200 con mensaje de exito
        $respuesta
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Contrasena actualizada exitosamente']);

        // verifico en la BD que la contraseña realmente cambio
        $usuario->refresh();
        $this->assertTrue(Hash::check('nueva_contrasena_123', $usuario->password));

        // verifico que borro todos los tokens viejos (por seguridad)
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // test 10: reset-password devuelve 400 con un token invalido o ya expirado
    public function test_reset_password_devuelve_400_con_token_invalido(): void
    {
        // creo el user
        User::factory()->create(['email' => 'alex@example.com']);

        // intento reset con un token falso
        $respuesta = $this->postJson('/api/auth/reset-password', [
            'token'                 => 'token-completamente-falso-xyz',
            'email'                 => 'alex@example.com',
            'password'              => 'nueva_contrasena_123',
            'password_confirmation' => 'nueva_contrasena_123',
        ]);

        // debe dar 400 porque el token no es valido
        $respuesta->assertStatus(400);
    }
}
