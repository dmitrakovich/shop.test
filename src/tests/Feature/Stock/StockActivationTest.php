<?php

namespace Tests\Feature\Stock;

use App\Enums\StockType;
use App\Facades\Device;
use App\Models\Stock;
use App\Repositories\StockRepository;
use Deliveries\DeliveryMethod;
use Deliveries\ShopPvz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class StockActivationTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    private Stock $shop;

    private StockRepository $stocks;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->stocks = app(StockRepository::class);

        $this->shop = Stock::factory()->create([
            'type' => StockType::SHOP,
            'is_active' => true,
            'address' => 'Green City test address',
        ]);
    }

    public function test_inactive_shop_is_hidden_from_shops_api(): void
    {
        $this->shop->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/shops', [
            'device-id' => self::DEVICE_ID,
        ])->assertOk();

        /** @var list<array{id: int}> $shops */
        $shops = $response->json('data') ?? [];
        $shopIds = array_column($shops, 'id');

        $this->assertNotContains($this->shop->id, $shopIds);
    }

    public function test_inactive_shop_is_hidden_from_stock_repository(): void
    {
        $this->shop->update(['is_active' => false]);

        $shopIds = $this->stocks->getShops()->pluck('id');

        $this->assertFalse($shopIds->contains($this->shop->id));
    }

    public function test_inactive_shop_is_excluded_from_shop_pvz_pickup_list(): void
    {
        $this->shop->update(['is_active' => false]);

        /** @var array<int, string> $shops */
        $shops = (new ShopPvz(Mockery::mock(DeliveryMethod::class)))
            ->getAdditionalInfo()
            ?->getData()['shops'] ?? [];

        $this->assertArrayNotHasKey($this->shop->id, $shops);
    }

    public function test_shop_address_options_exclude_inactive_except_current(): void
    {
        $activeShop = Stock::factory()->create([
            'type' => StockType::SHOP,
            'is_active' => true,
            'address' => 'Active shop',
        ]);
        $inactiveShop = Stock::factory()->inactive()->create([
            'type' => StockType::SHOP,
            'address' => 'Inactive shop',
        ]);
        $currentInactiveShop = Stock::factory()->inactive()->create([
            'type' => StockType::SHOP,
            'address' => 'Current inactive shop',
        ]);

        $options = $this->stocks->shopAddressOptions($currentInactiveShop->id);

        $this->assertTrue($options->has($activeShop->id));
        $this->assertTrue($options->has($this->shop->id));
        $this->assertFalse($options->has($inactiveShop->id));
        $this->assertTrue($options->has($currentInactiveShop->id));
    }
}
