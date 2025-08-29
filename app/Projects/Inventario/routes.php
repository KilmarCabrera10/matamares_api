<?php

use Illuminate\Support\Facades\Route;
use App\Projects\Inventario\Controllers\OrganizationController;

// === AUTHENTICATION ROUTES ===
Route::prefix('auth')->group(function () {
    // Rutas de autenticación específicas del inventario
});

// === PROTECTED ROUTES ===
Route::middleware('auth:sanctum')->group(function () {
    // Organizaciones
    Route::apiResource('organizations', OrganizationController::class);
    
    // Ubicaciones
    // Route::apiResource('locations', LocationController::class);
    
    // Categorías
    // Route::apiResource('categories', CategoryController::class);
    
    // Productos
    // Route::apiResource('products', ProductController::class);
    
    // Proveedores
    // Route::apiResource('suppliers', SupplierController::class);
    
    // Stock e inventario
    // Route::prefix('inventory')->group(function () {
    //     Route::get('stock', [InventoryController::class, 'stock']);
    //     Route::post('movements', [InventoryController::class, 'createMovement']);
    //     Route::get('movements', [InventoryController::class, 'getMovements']);
    // });
});