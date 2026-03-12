<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\IntelligenceController;
use App\Http\Controllers\DashboardController;

// ---------------------------------------------------------------
// Rutas públicas de autenticación
// throttle:auth → 5 intentos por minuto por IP (Issue #33)
// ---------------------------------------------------------------
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    // POST /api/auth/register → crea una cuenta nueva
    Route::post('/register', [AuthController::class, 'register']);

    // POST /api/auth/login → inicia sesion
    Route::post('/login', [AuthController::class, 'login']);

    // POST /api/auth/forgot-password → envia link de reset por email
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // POST /api/auth/reset-password → cambia la contrasena con el token del email
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// ---------------------------------------------------------------
// Rutas protegidas: requieren token de Sanctum
// ---------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // POST /api/auth/logout → cierra la sesion
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // GET /api/user → devuelve datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()
        ]);
    });

    // rutas de admin (token + rol admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', function (Request $request) {
            return response()->json([
                'users' => \App\Models\User::all()
            ]);
        });
    });

    // rutas de perfil (token + rol user)
    Route::middleware('role:user')->group(function () {
        Route::get('/profile', function (Request $request) {
            return response()->json([
                'profile' => $request->user()
            ]);
        });
    });

    // ---------------------------------------------------------------
    // Rutas de empresas: CRUD
    // throttle:data → 60 requests/min por usuario (Issue #33)
    // ---------------------------------------------------------------
    Route::middleware('throttle:data')->group(function () {
        Route::apiResource('companies', CompanyController::class);
    });

    // ---------------------------------------------------------------
    // Rutas de inteligencia: módulos principales
    // throttle:data → 60 requests/min por usuario (Issue #33)
    // ---------------------------------------------------------------
    Route::middleware('throttle:data')->prefix('intelligence')->group(function () {
        Route::get('/market',      [IntelligenceController::class, 'getMarketData']);
        Route::get('/trends',      [IntelligenceController::class, 'getTrendData']);
        Route::get('/predictions', [IntelligenceController::class, 'getPredictionData']);
        Route::get('/innovation',  [IntelligenceController::class, 'getInnovationData']);
    });

    // ---------------------------------------------------------------
    // Ruta del dashboard consolidado
    // throttle:dashboard → 30 requests/min por usuario (Issue #33)
    // ---------------------------------------------------------------
    Route::middleware('throttle:dashboard')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
