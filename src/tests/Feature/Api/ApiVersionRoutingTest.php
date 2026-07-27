<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionProperty;
use Tests\TestCase;

class ApiVersionRoutingTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        // AppServiceProvider sets a console device while PHPUnit boots.
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_unknown_api_version_returns_426(): void
    {
        $this->getJson('/api/v9/catalog', [
            'device-id' => self::DEVICE_ID,
        ])->assertStatus(426);
    }

    public function test_supported_v2_missing_route_returns_404_not_426(): void
    {
        $this->getJson('/api/v2/does-not-exist', [
            'device-id' => self::DEVICE_ID,
        ])->assertNotFound();
    }

    public function test_v2_catalog_route_is_registered(): void
    {
        $this->assertTrue(Route::has('api.v2.catalog.index'));
    }
}
