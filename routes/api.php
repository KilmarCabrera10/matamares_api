<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

// Ruta de prueba simple
Route::get('/test', function () {
    return response()->json([
        'message' => '¡La API está funcionando correctamente!',
        'timestamp' => now(),
        'server' => 'Matamares API',
        'laravel_version' => app()->version()
    ]);
});

// Ruta para verificar el estado de CSRF
Route::get('/csrf-status', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'message' => 'CSRF token disponible'
    ]);
});

// Rutas públicas de autenticación (funcionan con y sin CSRF)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas públicas para productos (sin autenticación)
Route::get('/products', [ProductController::class, 'index']);

// Rutas protegidas con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Rutas para productos (protegidas)
    Route::apiResource('products', ProductController::class)->except(['index']);
    
    // Rutas para roles (acceso básico para ver roles)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    
    // Rutas solo para administradores
    Route::middleware('role:admin')->group(function () {
        // Gestión de usuarios
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
        Route::post('/users/{user}/remove-role', [UserController::class, 'removeRole']);
        
        // Gestión de roles
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
    });
});
