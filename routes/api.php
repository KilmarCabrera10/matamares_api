<?php

use Illuminate\Support\Facades\Route;

// Ruta principal de información de la API
Route::get('/info', function () {
    return response()->json([
        'message' => 'API Multi-Proyecto Laravel',
        'projects' => array_keys(config('projects.projects')),
        'default_project' => config('projects.default'),
        'timestamp' => now(),
        'laravel_version' => app()->version()
    ]);
});

// Cargar rutas del proyecto activo o por defecto
$currentProject = config('projects.default');
$projectConfig = config("projects.projects.{$currentProject}");

if ($projectConfig && file_exists($projectConfig['routes_file'])) {
    // Agregar prefijo del proyecto a las rutas
    Route::prefix($currentProject)->group($projectConfig['routes_file']);
} else {
    // Fallback - cargar rutas de matamares directamente
    Route::prefix('matamares')->group(app_path('Projects/Matamares/routes.php'));
}

// Cargar rutas del sistema de inventario
Route::group([], base_path('routes/inventario.php'));
