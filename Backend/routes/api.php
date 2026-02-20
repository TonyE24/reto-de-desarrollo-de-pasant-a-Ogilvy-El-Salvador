<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// rutas publicas: cualquiera puede acceder sin necesitar token
Route::prefix('auth')->group(function () {
    // POST /api/auth/register → para crear una cuenta nueva
    Route::post('/register', [AuthController::class, 'register']);

    // POST /api/auth/login → para iniciar sesion
    Route::post('/login', [AuthController::class, 'login']);

    // POST /api/auth/forgot-password → manda el email con el link de reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // POST /api/auth/reset-password → cambia la contrasena con el token del email
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// rutas protegidas: solo si mandas el token en el header Authorization: Bearer {token}
Route::middleware('auth:sanctum')->group(function () {
    // POST /api/auth/logout → cierra la sesion y borra el token
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // GET /api/user → devuelve los datos del usuario que esta logueado
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });

    // rutas solo para admins: necesitan token + rol admin
    // ejemplo: ruta para ver todos los usuarios del sistema
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', function (Request $request) {
            return response()->json([
                'users' => \App\Models\User::all()
            ]);
        });
    });

    // rutas para usuarios normales: necesitan token + rol user
    // ejemplo: ruta para ver el perfil propio
    Route::middleware('role:user')->group(function () {
        Route::get('/profile', function (Request $request) {
            return response()->json([
                'profile' => $request->user()
            ]);
        });
    });
});
