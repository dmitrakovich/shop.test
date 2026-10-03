<?php

namespace Tests\Feature\Admin;

use App\Facades\Device;
use App\Models\Admin\AdminUser;
use App\Models\Product;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use ReflectionProperty;
use Scriptixru\SypexGeo\SypexGeoFacade;
use Tests\TestCase;

class LegacyAdminApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance('sxgeo', new class
        {
            public function getCountry(): string
            {
                return 'BY';
            }
        });
        SypexGeoFacade::clearResolvedInstance('sxgeo');

        // AppServiceProvider sets a console device while PHPUnit boots.
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_admin_api_middleware_uses_legacy_admin_auth_and_api_throttle(): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn (\Illuminate\Routing\Route $route): bool => $route->uri() === 'api/admin/stocks');

        $this->assertNotNull($route);
        $this->assertContains('throttle:admin-api', $route->gatherMiddleware());
        $this->assertNotContains('admin', $route->gatherMiddleware());

        $middleware = $this->app->make(Router::class)->gatherRouteMiddleware($route);

        $this->assertTrue(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'StartSession')),
        );
        $this->assertTrue(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'Encore\\Admin\\Middleware\\Authenticate')),
        );
        $this->assertTrue(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'ThrottleRequests')),
        );
        $this->assertFalse(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'LogOperation')),
        );
        $this->assertFalse(
            collect($middleware)->contains(fn (string $name): bool => str_contains($name, 'Admin\\Middleware\\Bootstrap')),
        );
    }

    public function test_guest_is_rejected_from_admin_api(): void
    {
        foreach ([
            '/api/admin/product/product',
            '/api/admin/product/data',
            '/api/admin/stocks',
        ] as $uri) {
            $this->forgetDevice();
            $this->get($uri)->assertRedirect('/admin/auth/login');
        }
    }

    public function test_authenticated_admin_can_load_order_form_lookups(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'api_admin',
            'password' => bcrypt('secret'),
            'name' => 'API Admin',
        ]);

        $product = Product::factory()->create([
            'category_id' => DB::table('categories')->value('id'),
            'brand_id' => DB::table('brands')->value('id'),
        ]);

        $headers = [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ];

        $this->forgetDevice();
        $this->actingAs($admin, 'admin')
            ->get('/api/admin/product/product?q=' . $product->id, $headers)
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page'])
            ->assertHeader('X-RateLimit-Limit', '600');

        $this->forgetDevice();
        $this->actingAs($admin, 'admin')
            ->get('/api/admin/product/data?productId=' . $product->id, $headers)
            ->assertOk()
            ->assertJsonStructure(['name', 'link', 'image', 'sizes']);

        $this->forgetDevice();
        $this->actingAs($admin, 'admin')
            ->get('/api/admin/stocks?productId=' . $product->id . '&sizeId=1', $headers)
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_old_admin_login_session_can_call_order_form_lookups(): void
    {
        AdminUser::query()->create([
            'username' => 'order_form_admin',
            'password' => bcrypt('secret'),
            'name' => 'Order Form Admin',
        ]);

        $product = Product::factory()->create([
            'category_id' => DB::table('categories')->value('id'),
            'brand_id' => DB::table('brands')->value('id'),
        ]);

        $this->forgetDevice();
        $this->get('/admin/auth/login')->assertOk();

        $this->forgetDevice();
        $this->post('/admin/auth/login', [
            'username' => 'order_form_admin',
            'password' => 'secret',
            '_token' => csrf_token(),
        ])->assertRedirect();

        $this->forgetDevice();
        $this->get('/api/admin/product/product?q=' . $product->id)
            ->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'next_page_url']);

        $this->forgetDevice();
        $this->get('/api/admin/product/data?productId=' . $product->id)
            ->assertOk()
            ->assertJsonStructure(['name', 'link', 'image', 'sizes']);

        $this->forgetDevice();
        $this->get('/api/admin/stocks?productId=' . $product->id . '&sizeId=1')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_storefront_user_cannot_read_admin_lookups(): void
    {
        $customer = User::withoutEvents(fn (): User => User::query()->create([
            'phone' => Phone::fromRawString('375291112233'),
        ]));
        $product = Product::factory()->create([
            'category_id' => DB::table('categories')->value('id'),
            'brand_id' => DB::table('brands')->value('id'),
        ]);

        $this->forgetDevice();
        $this->actingAs($customer)
            ->get('/api/admin/product/data?productId=' . $product->id)
            ->assertRedirect('/admin/auth/login')
            ->assertDontSee($product->slug, false);
    }

    public function test_order_form_lookup_burst_is_not_throttled(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'burst_admin',
            'password' => bcrypt('secret'),
            'name' => 'Burst Admin',
        ]);
        $product = Product::factory()->create([
            'category_id' => DB::table('categories')->value('id'),
            'brand_id' => DB::table('brands')->value('id'),
        ]);
        $urls = [
            '/api/admin/product/product?q=' . $product->id,
            '/api/admin/product/data?productId=' . $product->id,
            '/api/admin/stocks?productId=' . $product->id . '&sizeId=1',
        ];

        $this->actingAs($admin, 'admin');

        for ($attempt = 0; $attempt < 40; $attempt++) {
            $this->forgetDevice();
            $this->get($urls[$attempt % 3])->assertOk();
        }
    }

    private function forgetDevice(): void
    {
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }
}
