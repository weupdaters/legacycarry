<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In Laravel 9+, it's usually left null because of auto-imports.
     */
    protected $namespace = null;

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // API routes -> http://yourapp.test/api/...
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web routes -> http://yourapp.test/...
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
            // Admin routes -> http://yourapp.test/admin/...

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

        });
    }
}
