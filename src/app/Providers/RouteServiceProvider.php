<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RouteServiceProvider extends ServiceProvider
{
    public const string API_VERSION = 'v1';

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
        $this->mapApiFallbackRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware(['api', 'device.auth'])
            ->prefix('api/' . self::API_VERSION)
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
            ->prefix('api/external')
            ->as('api.external.')
            ->group(base_path('routes/api.external.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    protected function mapApiFallbackRoutes(): void
    {
        Route::any('api/{version?}/{path?}', function (Request $request, ?string $version = null) {
            if (str_contains($version, 'v') && $version !== self::API_VERSION) {
                abort(
                    Response::HTTP_UPGRADE_REQUIRED,
                    'API version is outdated. Please reload the page.'
                );
            }

            abort(404, "The route {$request->path()} could not be found.");
        })->where('path', '.*');
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
