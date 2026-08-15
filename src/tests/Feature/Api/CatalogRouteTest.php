<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionProperty;
use Tests\TestCase;

class CatalogRouteTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_v1_catalog_route_is_gone(): void
    {
        $this->getJson('/api/v1/catalog', [
            'device-id' => self::DEVICE_ID,
        ])->assertNotFound();
    }

    public function test_v2_catalog_uses_main_catalog_controller(): void
    {
        $route = Route::getRoutes()->getByName('api.v2.catalog.index');

        $this->assertNotNull($route);
        $this->assertSame(
            \App\Http\Controllers\Api\CatalogController::class . '@index',
            $route->getActionName(),
        );
    }
}
