<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        /** @var \Illuminate\Foundation\Application */
        $app = $this->app;

        if ($app->isProduction()) {
            $app['request']->server->set('HTTPS', 'on');
        }

        $this->configureRateLimiting();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapApiAdminRoutes();
        $this->mapApiExternalRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->as('api.')
            ->group(base_path('routes/api.php'));
    }

    protected function mapApiAdminRoutes(): void
    {
        Route::middleware(['api'])
            ->withoutMiddleware('throttle:api')
            ->prefix('api/admin')
            ->as('api.admin.')
            ->group(base_path('routes/api.admin.php'));
    }

    protected function mapApiExternalRoutes(): void
    {
        Route::middleware(['api'])
            // ->prefix('api/external')
            ->as('api.external.')
            ->group(base_path('routes/api.external.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
