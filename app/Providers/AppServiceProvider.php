<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar middleware personalizado
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('role', \App\Core\Middleware\CheckRole::class);
        
        // Configurar Sanctum para usar modelo personalizado de tokens
        Sanctum::usePersonalAccessTokenModel(\App\Projects\Inventario\Models\PersonalAccessToken::class);
    }
}
