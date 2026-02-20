<?php

namespace App\Http\Controllers;

// estas son las cosas que necesito importar para que todo funcione
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;    // para cifrar la contrasena
use Illuminate\Support\Facades\Auth;    // para verificar si el login es correcto
use Illuminate\Support\Facades\Password; // para el sistema de reset de contrasena
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // este metodo maneja el registro de nuevos usuarios
    // recibe los datos del frontend, los valida y crea el usuario en la BD
    public function register(Request $request)
    {
        // primero valido que los datos que llegaron esten bien
        // si algo falla laravel solo devuelve un 422 automaticamente
        $request->validate([
            'name'     => 'required|string|max:255',          // nombre obligatorio, max 255 caracteres
            'email'    => 'required|email|unique:users,email', // email valido y que no este repetido en la BD
            'password' => 'required|string|min:8|confirmed',  // minimo 8 caracteres y debe coincidir con password_confirmation
        ]);

        // creo el usuario con los datos que llegaron
        // la contrasena la guardo cifrada, nunca en texto plano
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user', // por defecto todos entran como usuarios normales
        ]);

        // genero el token de autenticacion para que el frontend lo guarde
        // este token es el que se usa en cada peticion para saber quien eres
        $token = $user->createToken('auth_token')->plainTextToken;

        // devuelvo los datos del usuario y el token
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 201); // 201 = recurso creado
    }

    // este metodo maneja el inicio de sesion
    // verifica que el email y contrasena sean correctos y devuelve un token
    public function login(Request $request)
    {
        // valido que lleguen email y contrasena
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Auth::attempt busca el usuario y verifica la contrasena
        // si algo esta mal devuelvo 401 (no autorizado)
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas, vuelve a intentarlo',
            ], 401);
        }

        // si las credenciales son correctas obtengo el usuario de la BD
        $user = User::where('email', $request->email)->first();

        // genero un nuevo token para esta sesion
        $token = $user->createToken('auth_token')->plainTextToken;

        // devuelvo los datos del usuario y el token
        return response()->json([
            'message' => 'Inicio de sesion exitoso',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 200);
    }

    // este metodo cierra la sesion del usuario
    // solo elimina el token actual, no todos los tokens del usuario
    public function logout(Request $request)
    {
        // borro el token con el que llego la peticion
        // asi ya no puede usarse mas para autenticarse
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesion cerrada exitosamente',
        ], 200);
    }

    // paso 1 del reset: el user manda su email y le enviamos un link
    // el link llega al inbox de Mailtrap cuando estamos en desarrollo
    public function forgotPassword(Request $request)
    {
        // solo necesito el email para buscar al usuario
        $request->validate([
            'email' => 'required|email',
        ]);

        // Laravel busca el user por email y manda el link de reset
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // si encontro el email y mando el link
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Te enviamos un link para restablecer tu contrasena'
            ], 200);
        }

        // si el email no existe en la BD
        return response()->json([
            'message' => 'No encontramos una cuenta con ese email'
        ], 404);
    }

    // paso 2 del reset: el user llega con el token del email y la nueva contrasena
    public function resetPassword(Request $request)
    {
        // necesito el token, el email y la nueva contrasena (con su confirmacion)
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // laravel verifica el token y si es valido cambia la contrasena
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // actualizo la contrasena del user
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                // borro todos los tokens viejos por seguridad
                // si alguien tenia acceso antes, ya no va a poder
                $user->tokens()->delete();
            }
        );

        // si todo salio bien
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Contrasena actualizada exitosamente'
            ], 200);
        }

        // si el token ya expiro o es invalido
        return response()->json([
            'message' => 'El token es invalido o ya expiro'
        ], 400);
    }
}
