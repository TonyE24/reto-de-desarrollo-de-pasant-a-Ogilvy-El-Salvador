<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|
| Rutas PÚBLICAS que no necesitan token
|
| Cualquier persona puede acceder a estas rutas sin estar autenticada
| Son las rutas de registro y login
*/
Route::prefix('auth')->group(function () {
    // La ruta  POST /api/auth/register → Registrar nuevo usuario
    Route::post('/register', [AuthController::class, 'register']);

    //La rutaPOST /api/auth/login → Iniciar sesión
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas PROTEGIDAS que si necesitaran el toquen de autenticación
|--------------------------------------------------------------------------
| Solo usuarios autenticados pueden acceder
| El frontend debe enviar el token en el header: Authorization: Bearer {token} para dar acceso a estas rutas
*/
Route::middleware('auth:sanctum')->group(function () {
    //La ruta POST /api/auth/logout → Cerrar sesión
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    //La ruta GET /api/user → Obtener datos del usuario actual
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });
});
