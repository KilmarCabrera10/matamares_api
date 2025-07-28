<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ReportController;

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

// === AUTHENTICATION ROUTES ===
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// === PROTECTED ROUTES ===
Route::middleware('auth:sanctum')->group(function () {
    
    // User info
    Route::get('/user', [AuthController::class, 'user']);
    
    // === PRODUCTS ===  
    Route::apiResource('products', ProductController::class);
    
    // === CUSTOMERS ===
    Route::apiResource('customers', CustomerController::class);
    
    // === SALES ===
    Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
    Route::get('/sales/today-stats', [SaleController::class, 'todayStats']);
    
    // === ROLES === (Basic access for all authenticated users)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);
    
    // === REPORTS === (Manager and Admin access - add middleware later)
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales']);
        Route::get('/products', [ReportController::class, 'products']);
        Route::get('/users', [ReportController::class, 'users']);
    });
    
    // === ADMIN ONLY ROUTES ===
    Route::middleware('role:administrador')->prefix('admin')->group(function () {
        
        // User management
        Route::apiResource('users', UserController::class);
        Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        
        // Role management
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
        
    });
});
