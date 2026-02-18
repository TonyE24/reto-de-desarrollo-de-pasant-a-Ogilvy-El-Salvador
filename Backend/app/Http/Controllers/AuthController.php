<?php

namespace App\Http\Controllers;

// Importamos las herramientas que vamos a necesitar
use App\Models\User;                    // El modelo de usuario (representa la tabla "users")
use Illuminate\Http\Request;            // Para recibir los datos del formulario
use Illuminate\Support\Facades\Hash;    // Para cifrar contraseñas
use Illuminate\Support\Facades\Auth;    // Para verificar credenciales en el login
use Illuminate\Validation\ValidationException; // Para manejar errores de validación

class AuthController extends Controller
{
    /**
     * ESTA ES LA FUNCIÓN PARA REGISTRO DE USUARIO
     * 
     * Este método recibe los datos del formulario de registro valida que sean correctos.     * .
     * 
     * Ruta para registrar al usuario es: POST /api/auth/register
     */
    public function register(Request $request)
    {
        // PASO 1: Validamos los datos que llegan del frontend registrado
        // Si alguna validación falla entonces Laravel automáticamente devuelve un error 422
        $request->validate([
            'name'     => 'required|string|max:255',           // EL campo Nombre tiene las validaciones que es: obligatorio, texto, máximo 255 caracteres
        'email'    => 'required|email|unique:users,email',     // EL campo Email tiene las validaciones que es: obligatorio, formato válido, único en la tabla users
            'password' => 'required|string|min:8|confirmed',   // EL campo Contraseña tiene las validaciones que es: obligatorio, mínimo 8 caracteres, debe confirmarse
        ]);

        // PASO 2: Creamos el usuario en la base de datos una vez que han validados los datos 
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Hash::make() cifra la contraseña nunca y no la guardamos como texto plano
            'role'     => 'user',                         // Por defecto todos los nuevos usuarios son "user" hasta que un administrador decida hacerlos admins
        ]);

        // PASO 3: Creamos un token de autenticación para este usuario nuevo
        // El token es como una "llave" que el frontend guardará y usará en cada petición para validar las peticiones
        $token = $user->createToken('auth_token')->plainTextToken;

        // PASO 4: Devolveremos la respuesta al frontend
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token, // En el frontend guardará este token para futuras peticiones
        ], 201); // El código 201 significa "Created" recurso creado exitosamente y aqui termina el registro.
    }

    /**
     * ESTA ES LA FUNCIÓN PARA INICIO DE SESIÓN
     * 
     * Este método verifica las credenciales del usuario y devuelve un token en caso de que las credenciales sean correctas.
     * 
     *  Esta es la ruta para iniciar sesión: POST /api/auth/login
     */
    public function login(Request $request)
    {
        // PASO 1: Validamos que lleguen email y contraseña
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // PASO 2: Intentaremos autenticar al usuario
        // la función Auth::attempt() busca el usuario por email y verifica la contraseña sea valida
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son incorrectas entonces devolvemos error 401
            return response()->json([
                'message' => 'Credenciales que ingreso son incorrectas',
            ], 401); // El código 401 = "Unauthorized" no tiene permiso para acceder
        }

        // PASO 3: Obtenemos el usuario autenticado una vez que las credenciales son correctas
        $user = User::where('email', $request->email)->first();

        // PASO 4: Creamos un nuevo token para esta sesión
        $token = $user->createToken('auth_token')->plainTextToken;

        // PASO 5: Devolvemos el token al frontend para que lo guarde
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 200); // El código 200 = "OK" todo salió bien el usuario ya puede usar la aplicación felicidades
    }

    /**
     * ESTA ES LA FUNCIÓN PARA CERRAR SESIÓN
     * 
     * Este método elimina el token del usuario cierra la sesión del usuario y no podrá usar la aplicación hasta que inicie sesión nuevamente
     * 
     * Esta es la ruta para cerrar sesión: POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        // Eliminamos el token actual del usuario
        // Esto invalida la llave para que no pueda usarse más en futuras peticiones
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ], 200); 
    }
}
