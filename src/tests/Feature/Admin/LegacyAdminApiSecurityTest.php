<?php

namespace Tests\Feature\Admin;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LegacyAdminApiSecurityTest extends TestCase
{
    public function test_legacy_admin_api_stays_stateless_until_the_old_admin_is_removed(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn (\Illuminate\Routing\Route $route): bool => $route->uri() === 'api/admin/product/data');

        $this->assertNotNull($route);
        $this->assertContains('api', $route->gatherMiddleware());
        $this->assertContains('throttle:api', $route->excludedMiddleware());
        $this->assertNotContains('web', $route->gatherMiddleware());
        $this->assertNotContains('admin.auth', $route->gatherMiddleware());
        $this->assertNotContains('throttle:admin-api', $route->gatherMiddleware());

        $middleware = $this->app->make(Router::class)->gatherRouteMiddleware($route);

        $this->assertFalse(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'StartSession')),
        );
        $this->assertFalse(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'Encore\\Admin\\Middleware\\Authenticate')),
        );
        $this->assertFalse(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'ThrottleRequests')),
        );
    }
}
