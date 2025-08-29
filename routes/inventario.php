<?php

use Illuminate\Support\Facades\Route;
use App\Projects\Inventario\Controllers\OrganizationController;
use App\Projects\Inventario\Controllers\ProductController;
use App\Projects\Inventario\Controllers\LocationController;
use App\Projects\Inventario\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| Inventario API Routes
|--------------------------------------------------------------------------
|
| Rutas del sistema de inventario con multitenancy
|
*/

Route::prefix('inventario')->group(function () {
    
    // Rutas públicas (sin autenticación)
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'service' => 'inventario']);
    });

    // Rutas protegidas con autenticación Sanctum
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Gestión de organizaciones (no requiere Organization-Id)
        Route::apiResource('organizations', OrganizationController::class);
        
        // Rutas que requieren multitenancy (Organization-Id header)
        Route::middleware(['multitenant'])->group(function () {
            
            // Productos
            Route::prefix('products')->group(function () {
                Route::get('/', [ProductController::class, 'index']);
                Route::post('/', [ProductController::class, 'store']);
                Route::get('/{id}', [ProductController::class, 'show']);
                Route::put('/{id}', [ProductController::class, 'update']);
                Route::delete('/{id}', [ProductController::class, 'destroy']);
                Route::get('/search/{term}', [ProductController::class, 'search']);
                Route::get('/low-stock/alert', [ProductController::class, 'lowStock']);
            });

            // Ubicaciones
            Route::prefix('locations')->group(function () {
                Route::get('/', [LocationController::class, 'index']);
                Route::post('/', [LocationController::class, 'store']);
                Route::get('/{id}', [LocationController::class, 'show']);
                Route::put('/{id}', [LocationController::class, 'update']);
                Route::delete('/{id}', [LocationController::class, 'destroy']);
                Route::get('/{id}/stock', [LocationController::class, 'stock']);
                Route::post('/{fromLocationId}/transfer/{toLocationId}', [LocationController::class, 'transfer']);
                Route::get('/{id}/statistics', [LocationController::class, 'statistics']);
            });

            // Inventario y movimientos
            Route::prefix('inventory')->group(function () {
                Route::get('/dashboard', [InventoryController::class, 'dashboard']);
                Route::get('/stock', [InventoryController::class, 'stock']);
                Route::get('/movements', [InventoryController::class, 'movements']);
                Route::post('/movements', [InventoryController::class, 'createMovement']);
                Route::get('/transaction-types', [InventoryController::class, 'transactionTypes']);
                Route::get('/reports', [InventoryController::class, 'reports']);
            });
        });
    });
});
