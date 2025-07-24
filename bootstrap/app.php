<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Solo aplicar middleware stateful a rutas específicas si es necesario
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Opcional: Excluir CSRF de ciertas rutas API si es necesario
        $middleware->validateCsrfTokens(except: [
            'api/*', // Deshabilita CSRF para todas las rutas API (usa solo tokens)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
