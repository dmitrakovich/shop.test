<?php

namespace Tests\Feature\Catalog;

use App\Facades\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use ReflectionProperty;
use Tests\TestCase;

class LegacyStorefrontRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // AppServiceProvider sets a console device while PHPUnit boots; web
        // DeviceDetect middleware must be able to bind the request device.
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_legacy_storefront_paths_fall_through_to_frontend(): void
    {
        config(['app.front_url' => 'http://front.test']);

        $this->get('/')
            ->assertRedirect('http://front.test/');

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->get('/catalog/shoes?sort=newness')
            ->assertRedirect('http://front.test/catalog/shoes?sort=newness');

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->get('/product/some-slug')
            ->assertRedirect('http://front.test/product/some-slug');
    }

    public function test_legacy_blade_storefront_routes_are_unregistered(): void
    {
        $uris = collect(Route::getRoutes())->map(fn ($route) => $route->uri());

        $this->assertFalse(Route::has('index-page'));
        $this->assertFalse(Route::has('shop'));
        $this->assertFalse($uris->contains('catalog/{path?}'));
        $this->assertFalse($uris->contains('product/{product}'));
        $this->assertFalse($uris->contains('ajax-next-page'));
        $this->assertFalse($uris->contains('price-filter/{path?}'));
        $this->assertTrue(Route::has('api.product.show'));
    }
}
