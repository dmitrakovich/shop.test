<?php

namespace Tests\Feature\Filament\Orders;

use App\Enums\Order\OrderItemStatusLogStage;
use App\Filament\Resources\Orders\OrderItemStatusLogs\OrderItemStatusLogResource;
use App\Filament\Resources\Orders\OrderItemStatusLogs\Pages\ListOrderItemStatusLogs;
use App\Models\Admin\AdminUser;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Models\Size;
use App\Models\Stock;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderItemStatusLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'admin');
        $this->stock = Stock::factory()->create(['internal_name' => 'ТЦ Тестовый']);
    }

    public function test_movement_page_shows_unit_product_order_stock_and_stage(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create(['sku' => 'SKU-MOVE-1']);
        $size = Size::factory()->create(['name' => '39.5']);
        $log = $this->createLog($this->createOrderItem($order, $product, $size), [
            'confirmed_at' => now()->subHour(),
            'collected_at' => now(),
        ]);

        $this->get(OrderItemStatusLogResource::getUrl())->assertOk();

        Livewire::test(ListOrderItemStatusLogs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$log])
            ->assertTableColumnStateSet('stage', OrderItemStatusLogStage::Collected, $log)
            ->assertSee('SKU-MOVE-1')
            ->assertSee('39.5')
            ->assertSee('ТЦ Тестовый')
            ->assertSee(url("old-admin/orders/{$order->id}/edit"));
    }

    public function test_out_of_stock_units_and_units_of_deleted_order_items_are_listed(): void
    {
        $order = $this->createOrder();
        $outOfStock = $this->createLog($this->createOrderItem($order));
        $outOfStock->delete();

        $orphan = $this->createLog($this->createOrderItem($order));
        OrderItem::query()->whereKey($orphan->order_item_id)->delete();

        Livewire::test(ListOrderItemStatusLogs::class)
            ->assertCanSeeTableRecords([$outOfStock, $orphan])
            ->assertTableColumnStateSet('stage', OrderItemStatusLogStage::OutOfStock, $outOfStock)
            ->assertSee('Позиция удалена из заказа');
    }

    public function test_movements_can_be_searched_by_product_id_and_sku(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create(['sku' => 'ABC-555']);
        $productLog = $this->createLog($this->createOrderItem($order, $product));
        $otherLog = $this->createLog($this->createOrderItem($order, Product::factory()->create(['sku' => 'XYZ-999'])));

        Livewire::test(ListOrderItemStatusLogs::class)
            ->searchTable((string)$product->id)
            ->assertCanSeeTableRecords([$productLog])
            ->assertCanNotSeeTableRecords([$otherLog])
            ->searchTable('c-55')
            ->assertCanSeeTableRecords([$productLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    public function test_movements_can_be_filtered_by_order_stage_and_stock(): void
    {
        $order = $this->createOrder();
        $otherOrder = $this->createOrder();
        $otherStock = Stock::factory()->create();

        $completed = $this->createLog($this->createOrderItem($order), ['completed_at' => now()]);
        $returned = $this->createLog($this->createOrderItem($order), ['completed_at' => now(), 'returned_at' => now()]);
        $otherOrderLog = $this->createLog($this->createOrderItem($otherOrder), [], $otherStock);

        Livewire::test(ListOrderItemStatusLogs::class)
            ->filterTable('order_id', ['order_id' => $order->id])
            ->assertCanSeeTableRecords([$completed, $returned])
            ->assertCanNotSeeTableRecords([$otherOrderLog])
            ->resetTableFilters()
            ->filterTable('stage', OrderItemStatusLogStage::Completed->value)
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$returned, $otherOrderLog])
            ->resetTableFilters()
            ->filterTable('stock_id', [$otherStock->id])
            ->assertCanSeeTableRecords([$otherOrderLog])
            ->assertCanNotSeeTableRecords([$completed, $returned]);
    }

    private function createOrder(): Order
    {
        return Order::withoutEvents(fn (): Order => Order::query()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString('+375291112233'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
        ]));
    }

    private function createOrderItem(Order $order, ?Product $product = null, ?Size $size = null): OrderItem
    {
        $product ??= Product::factory()->create();
        $size ??= Size::factory()->create();

        return OrderItem::withoutEvents(fn (): OrderItem => $order->items()->create([
            'product_id' => $product->id,
            'size_id' => $size->id,
            'count' => 1,
            'buy_price' => 50,
            'price' => 100,
            'old_price' => 0,
            'current_price' => 100,
        ]));
    }

    /**
     * @param  array<string, mixed>  $dates
     */
    private function createLog(OrderItem $orderItem, array $dates = [], ?Stock $stock = null): OrderItemStatusLog
    {
        return OrderItemStatusLog::query()->create([
            'order_item_id' => $orderItem->id,
            'stock_id' => ($stock ?? $this->stock)->id,
            'created_at' => now(),
            ...$dates,
        ]);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'item_movement_admin',
            'password' => bcrypt('secret'),
            'name' => 'Item Movement',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
