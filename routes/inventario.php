<?php

use Illuminate\Support\Facades\Route;
use App\Projects\Inventario\Controllers\OrganizationController;
use App\Projects\Inventario\Controllers\ProductController;
use App\Projects\Inventario\Controllers\LocationController;
use App\Projects\Inventario\Controllers\InventoryController;
use App\Projects\Inventario\Controllers\AuthController;
use App\Projects\Inventario\Controllers\CuadreController;

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

    // Rutas de autenticación (públicas)
    Route::prefix('auth')->group(function () {
        Route::post('/login',    [AuthController::class, 'login'   ]);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Rutas protegidas con autenticación Sanctum
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Rutas de autenticación protegidas
        Route::prefix('auth')->group(function () {
            Route::post('/logout',          [AuthController::class, 'logout']        );
            Route::get('/me',               [AuthController::class, 'me']            );
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
        
        // Gestión de organizaciones (no requiere Organization-Id)
        Route::apiResource('organizations', OrganizationController::class);
        
        // Rutas que requieren multitenancy (Organization-Id header)
        Route::middleware(['multitenant'])->group(function () {
            
            // Productos
            Route::prefix('products')->group(function () {
                Route::get('/',                [ProductController::class, 'index']   );
                Route::post('/',               [ProductController::class, 'store']   );
                Route::get('/{id}',            [ProductController::class, 'show']    );
                Route::put('/{id}',            [ProductController::class, 'update']  );
                Route::delete('/{id}',         [ProductController::class, 'destroy'] );
                Route::get('/search/{term}',   [ProductController::class, 'search']  );
                Route::get('/low-stock/alert', [ProductController::class, 'lowStock']);
            });

            // Ubicaciones
            Route::prefix('locations')->group(function () {
                Route::get('/',                                          [LocationController::class, 'index']     );
                Route::post('/',                                         [LocationController::class, 'store']     );
                Route::get('/{id}',                                      [LocationController::class, 'show']      );
                Route::put('/{id}',                                      [LocationController::class, 'update']    );
                Route::delete('/{id}',                                   [LocationController::class, 'destroy']   );
                Route::get('/{id}/stock',                                [LocationController::class, 'stock']     );
                Route::post('/{fromLocationId}/transfer/{toLocationId}', [LocationController::class, 'transfer']  );
                Route::get('/{id}/statistics',                           [LocationController::class, 'statistics']);
            });

            // Inventario y movimientos
            Route::prefix('inventory')->group(function () {
                Route::get('/dashboard',         [InventoryController::class, 'dashboard']       );
                Route::get('/stock',             [InventoryController::class, 'stock']           );
                Route::get('/movements',         [InventoryController::class, 'movements']       );
                Route::post('/movements',        [InventoryController::class, 'createMovement']  );
                Route::get('/transaction-types', [InventoryController::class, 'transactionTypes']);
                Route::get('/reports',           [InventoryController::class, 'reports']         );
            });

            // Cuadres de caja
            Route::prefix('cuadres')->group(function () {
                Route::get('/saldo-anterior', [CuadreController::class, 'saldoAnterior']);
                Route::get('/historial',      [CuadreController::class, 'historial']    );
                Route::post('/',              [CuadreController::class, 'store']        );
                Route::get('/validar-fecha',  [CuadreController::class, 'validarFecha'] );
                Route::get('/fecha/{fecha}',  [CuadreController::class, 'porFecha']     );
                Route::put('/{id}',           [CuadreController::class, 'update']       );
                Route::delete('/{id}',        [CuadreController::class, 'destroy']      );
            });

            // Estadísticas de movimientos
            Route::prefix('movimientos')->group(function () {
                Route::get('/estadisticas-dia', [CuadreController::class, 'estadisticasDia']);
            });
        });
    });
});
