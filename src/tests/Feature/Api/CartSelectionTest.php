<?php

namespace Tests\Feature\Api;

use App\Data\Order\OrderData;
use App\Enums\Promo\SaleAlgorithm;
use App\Events\Analytics\Registered;
use App\Events\Order\OrderCreated;
use App\Facades\Device;
use App\Models\Cart;
use App\Models\CartData;
use App\Models\Product;
use App\Models\Promo\Sale;
use App\Models\Size;
use App\Models\User\Device as UserDevice;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CartSelectionTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    private Cart $cart;

    private CartData $selectedItem;

    private CartData $unselectedItem;

    private Size $size;

    private UserDevice $device;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->device = UserDevice::query()->create([
            'api_id' => self::DEVICE_ID,
            'web_id' => 'cart-selection-web-id',
        ]);

        $this->cart = Cart::query()->create([
            'device_id' => $this->device->id,
        ]);

        $this->size = Size::factory()->create();
        $selectedProduct = Product::factory()->create([
            'price' => 100,
            'old_price' => 0,
        ]);
        $unselectedProduct = Product::factory()->create([
            'price' => 200,
            'old_price' => 0,
        ]);
        $selectedProduct->sizes()->attach($this->size->id);
        $unselectedProduct->sizes()->attach($this->size->id);

        $this->selectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $selectedProduct->id,
            'size_id' => $this->size->id,
            'count' => 1,
            'selected' => true,
        ]);

        $this->unselectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $unselectedProduct->id,
            'size_id' => $this->size->id,
            'count' => 1,
            'selected' => false,
        ]);
    }

    public function test_cart_items_expose_selected_flag(): void
    {
        $response = $this->getJson('/api/v1/cart', $this->deviceHeaders())
            ->assertOk();

        /** @var array<int, array{id: int, selected: bool}> $itemsPayload */
        $itemsPayload = $response->json('items');
        $items = collect($itemsPayload);

        $this->assertTrue($items->firstWhere('id', $this->selectedItem->id)['selected']);
        $this->assertFalse($items->firstWhere('id', $this->unselectedItem->id)['selected']);
    }

    public function test_can_toggle_item_selection(): void
    {
        $this->postJson(
            '/api/v1/cart/items/' . $this->selectedItem->id . '/selected',
            ['selected' => false],
            $this->deviceHeaders()
        )->assertOk();

        $this->assertFalse($this->selectedItem->fresh()->selected);
    }

    public function test_can_select_all_items(): void
    {
        $this->postJson('/api/v1/cart/select-all', ['selected' => true], $this->deviceHeaders())
            ->assertOk();

        $this->assertTrue($this->selectedItem->fresh()->selected);
        $this->assertTrue($this->unselectedItem->fresh()->selected);
    }

    public function test_can_deselect_all_items(): void
    {
        $this->postJson('/api/v1/cart/select-all', ['selected' => false], $this->deviceHeaders())
            ->assertOk();

        $this->assertFalse($this->selectedItem->fresh()->selected);
        $this->assertFalse($this->unselectedItem->fresh()->selected);
    }

    public function test_can_remove_selected_items(): void
    {
        $this->deleteJson('/api/v1/cart/selected', [], $this->deviceHeaders())
            ->assertOk()
            ->assertJsonCount(1, 'items');

        $this->assertDatabaseMissing('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
    }

    public function test_clear_selected_keeps_unselected_items(): void
    {
        $this->cart->load('items.product.sizes');
        $this->cart->clearSelected();

        $this->assertDatabaseMissing('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
        $this->assertFalse($this->cart->fresh()->load('items')->hasSelectedAvailableItems());
    }

    public function test_clear_selected_keeps_unavailable_selected_items(): void
    {
        $unavailableProduct = Product::factory()->create();
        $unavailableProduct->sizes()->attach($this->size->id);

        $unavailableSelected = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $unavailableProduct->id,
            'size_id' => $this->size->id,
            'count' => 1,
            'selected' => true,
        ]);

        $unavailableProduct->delete();

        $cart = app(CartService::class)->prepareCart(
            $this->cart->fresh()->load('items.product.sizes')
        );
        $cart->clearSelected();

        $this->assertDatabaseMissing('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $unavailableSelected->id]);
    }

    public function test_checkout_orders_only_selected_available_and_keeps_the_rest(): void
    {
        Event::fake([
            OrderCreated::class,
            Registered::class,
        ]);
        Device::setDevice($this->device);

        $unavailableProduct = Product::factory()->create([
            'price' => 50,
            'old_price' => 0,
        ]);
        $unavailableProduct->sizes()->attach($this->size->id);

        $unavailableSelected = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $unavailableProduct->id,
            'size_id' => $this->size->id,
            'count' => 1,
            'selected' => true,
        ]);

        $unavailableProduct->delete();

        $cart = app(CartService::class)->prepareCart(
            $this->cart->fresh()->load('items.product.sizes')
        );

        $order = app(OrderService::class)->store($cart, OrderData::from([
            'first_name' => 'Ivan',
            'phone' => '+375291112233',
        ]));

        $this->assertCount(1, $order->items);
        $this->assertSame($this->selectedItem->product_id, $order->items->first()->product_id);

        $this->assertDatabaseMissing('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $unavailableSelected->id]);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_checkout_requires_selected_available_items(): void
    {
        Device::setDevice($this->device);
        $this->selectedItem->update(['selected' => false]);

        $cart = app(CartService::class)->prepareCart(
            $this->cart->fresh()->load('items.product.sizes')
        );

        try {
            app(OrderService::class)->store($cart, OrderData::from([
                'first_name' => 'Ivan',
                'phone' => '+375291112233',
            ]));
            $this->fail('Expected checkout to abort without selected available items.');
        } catch (HttpException $exception) {
            $this->assertSame(422, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
    }

    public function test_apply_to_cart_keeps_independent_sale_state_for_same_product(): void
    {
        Device::setDevice($this->device);

        Sale::query()->create([
            'title' => 'Cart selection sale',
            'label_text' => 'Test sale',
            'start_datetime' => now()->subHour(),
            'end_datetime' => now()->addDay(),
            'algorithm' => SaleAlgorithm::SIMPLE,
            'sale_percentage' => '0.1',
            'only_new' => false,
            'only_discount' => false,
            'add_client_sale' => false,
            'add_review_sale' => false,
            'has_installment' => true,
            'has_cod' => true,
            'has_fitting' => true,
        ]);
        $this->app->forgetInstance(SaleService::class);

        $secondSize = Size::factory()->create();
        $product = Product::factory()->create([
            'price' => 100,
            'old_price' => 0,
        ]);
        $product->sizes()->attach([$this->size->id, $secondSize->id]);

        $this->cart->items()->delete();

        $selectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'size_id' => $this->size->id,
            'count' => 1,
            'selected' => true,
        ]);
        $unselectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $product->id,
            'size_id' => $secondSize->id,
            'count' => 1,
            'selected' => false,
        ]);

        $cart = $this->cart->fresh()->load(['items.product.styles', 'items.size']);
        $sharedProduct = $cart->items->first()->product;
        foreach ($cart->items as $item) {
            $item->setRelation('product', $sharedProduct);
        }

        app(SaleService::class)->applyToCart($cart);

        $selectedLine = $cart->items->firstWhere('id', $selectedItem->id);
        $unselectedLine = $cart->items->firstWhere('id', $unselectedItem->id);

        $this->assertNotSame($selectedLine->product, $unselectedLine->product);
        $this->assertNotNull($selectedLine->product->getSale(SaleService::GENERAL_SALE_KEY));
        $this->assertNull($unselectedLine->product->getSale(SaleService::GENERAL_SALE_KEY));
        $this->assertSame(90.0, $selectedLine->product->getFinalPrice());
        $this->assertSame(100.0, $unselectedLine->product->getFinalPrice());
    }

    /**
     * @return array<string, string>
     */
    private function deviceHeaders(): array
    {
        return ['device-id' => self::DEVICE_ID];
    }
}
