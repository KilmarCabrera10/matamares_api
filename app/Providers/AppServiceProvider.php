<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

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
    }
}
