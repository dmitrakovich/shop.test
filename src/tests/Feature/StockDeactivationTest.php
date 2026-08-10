<?php

namespace Tests\Feature;

use App\Enums\StockTypeEnum;
use App\Facades\Device;
use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Models\Stock;
use App\Services\StockService;
use Deliveries\DeliveryMethod;
use Deliveries\ShopPvz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class StockDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    private Stock $shop;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->shop = Stock::query()
            ->where('type', StockTypeEnum::SHOP)
            ->where('is_active', true)
            ->firstOrFail();
    }

    public function test_inactive_shop_is_hidden_from_shops_api(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $this->shop->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/shops', [
            'device-id' => self::DEVICE_ID,
        ])->assertOk();

        /** @var list<array{id: int}> $shops */
        $shops = $response->json('data') ?? [];
        $shopIds = array_column($shops, 'id');

        $this->assertNotContains($this->shop->id, $shopIds);
    }

    public function test_inactive_shop_is_hidden_from_stock_service(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $this->shop->update(['is_active' => false]);

        $shopIds = app(StockService::class)->getShops()->pluck('id');

        $this->assertFalse($shopIds->contains($this->shop->id));
    }

    public function test_inactive_shop_is_excluded_from_shop_pvz_pickup_list(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $this->shop->update(['is_active' => false]);

        /** @var array<int, string> $shops */
        $shops = (new ShopPvz(Mockery::mock(DeliveryMethod::class)))
            ->getAdditionalInfo()
            ?->getData()['shops'] ?? [];

        $this->assertArrayNotHasKey($this->shop->id, $shops);
    }

    public function test_deactivating_stock_removes_its_available_sizes_and_dispatches_catalog_sync(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        DB::table('available_sizes')->insert([
            'product_id' => 1,
            'one_c_product_id' => 1,
            'brand_id' => 1,
            'category_id' => 1,
            'stock_id' => $this->shop->id,
            'sku' => 'TEST-SKU',
            'category_name' => 'test',
            'buy_price' => 10,
            'sell_price' => 20,
            'size_36' => 2,
        ]);

        $this->shop->update(['is_active' => false]);

        $this->assertDatabaseMissing('available_sizes', [
            'stock_id' => $this->shop->id,
        ]);

        Bus::assertDispatched(UpdateAvailabilityJob::class, function (UpdateAvailabilityJob $job) {
            return (new ReflectionProperty($job, 'syncFromOneC'))->getValue($job) === false;
        });
    }

    public function test_reactivating_stock_dispatches_availability_sync_from_one_c(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $this->shop->update(['is_active' => false]);
        Bus::assertDispatchedTimes(UpdateAvailabilityJob::class, 1);

        $this->shop->update(['is_active' => true]);

        Bus::assertDispatched(UpdateAvailabilityJob::class, function (UpdateAvailabilityJob $job) {
            return (new ReflectionProperty($job, 'syncFromOneC'))->getValue($job) === true;
        });
    }
}
