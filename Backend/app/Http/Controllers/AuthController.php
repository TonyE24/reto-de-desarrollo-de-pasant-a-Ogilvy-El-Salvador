<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // registro de usuarios nuevos
    public function register(RegisterRequest $request)
    {
        // los datos ya vienen validados y sanitizados por el FormRequest
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token,
        ], 201);
    }

    // inicio de sesion
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas, vuelve a intentarlo',
            ], 401);
        }

        $user  = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

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

    // cierre de sesion: elimina solo el token actual
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesion cerrada exitosamente',
        ], 200);
    }

    // paso 1 del reset: manda el link al email
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Te enviamos un link para restablecer tu contrasena'
            ], 200);
        }

        return response()->json([
            'message' => 'No encontramos una cuenta con ese email'
        ], 404);
    }

    // paso 2 del reset: cambia la contrasena con el token del email
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                // borramos todos los tokens viejos por seguridad
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Contrasena actualizada exitosamente'
            ], 200);
        }

        return response()->json([
            'message' => 'El token es invalido o ya expiro'
        ], 400);
    }
}
