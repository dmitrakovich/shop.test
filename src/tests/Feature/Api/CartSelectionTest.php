<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use App\Models\Cart;
use App\Models\CartData;
use App\Models\Product;
use App\Models\Size;
use App\Models\User\Device as UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class CartSelectionTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    private Cart $cart;

    private CartData $selectedItem;

    private CartData $unselectedItem;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $device = UserDevice::query()->create([
            'api_id' => self::DEVICE_ID,
            'web_id' => 'cart-selection-web-id',
        ]);

        $this->cart = Cart::query()->create([
            'device_id' => $device->id,
        ]);

        $size = Size::factory()->create();
        $selectedProduct = Product::factory()->create();
        $unselectedProduct = Product::factory()->create();
        $selectedProduct->sizes()->attach($size->id);
        $unselectedProduct->sizes()->attach($size->id);

        $this->selectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $selectedProduct->id,
            'size_id' => $size->id,
            'count' => 1,
            'selected' => true,
        ]);

        $this->unselectedItem = CartData::query()->create([
            'cart_id' => $this->cart->id,
            'product_id' => $unselectedProduct->id,
            'size_id' => $size->id,
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
        $this->cart->load('items');
        $this->cart->clearSelected();

        $this->assertDatabaseMissing('cart_data', ['id' => $this->selectedItem->id]);
        $this->assertDatabaseHas('cart_data', ['id' => $this->unselectedItem->id]);
        $this->assertFalse($this->cart->fresh()->load('items')->hasSelectedAvailableItems());
    }

    /**
     * @return array<string, string>
     */
    private function deviceHeaders(): array
    {
        return ['device-id' => self::DEVICE_ID];
    }
}
