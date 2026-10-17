<?php

namespace Tests\Feature\Filament\Orders;

use App\Enums\Order\OrderItemStatus;
use App\Exports\OrderItemsExport;
use App\Filament\Resources\Orders\OrderItems\OrderItemResource;
use App\Filament\Resources\Orders\OrderItems\Pages\ListOrderItems;
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
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderItemResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'admin');
    }

    public function test_order_items_page_shows_product_order_status_and_stock(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create(['sku' => 'SKU-ITEM-1']);
        $item = $this->createOrderItem($order, OrderItemStatus::SENT, $product, Size::factory()->create(['name' => '40.5']));
        $this->createStatusLog($item, Stock::factory()->create(['internal_name' => 'Склад Тест']), ['sended_at' => now()]);

        $this->get(OrderItemResource::getUrl())->assertOk();

        Livewire::test(ListOrderItems::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$item])
            ->assertSee('SKU-ITEM-1')
            ->assertSee("код {$product->id}")
            ->assertSee('40.5')
            ->assertSee('Отправлен')
            ->assertSee('Склад Тест')
            ->assertSee(url("old-admin/orders/{$order->id}/edit"));
    }

    public function test_items_of_deleted_products_are_marked_explicitly(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create();
        $deletedProductItem = $this->createOrderItem($order, product: $product);
        $product->forceDelete();

        Livewire::test(ListOrderItems::class)
            ->assertCanSeeTableRecords([$deletedProductItem])
            ->assertTableColumnFormattedStateSet('product_id', 'Товар удалён', $deletedProductItem)
            ->assertDontSee('BAROCCO Товар удалён');

        $this->assertSame('Товар удалён', new OrderItemsExport(OrderItem::query())->map($deletedProductItem->fresh())[2]);
    }

    public function test_order_items_can_be_searched_by_product_id_and_sku(): void
    {
        $order = $this->createOrder();
        $product = Product::factory()->create(['sku' => 'ABC-QQQ']);
        $productItem = $this->createOrderItem($order, product: $product);
        $otherItem = $this->createOrderItem($order, product: Product::factory()->create(['sku' => 'XYZ-WWW']));

        Livewire::test(ListOrderItems::class)
            ->searchTable((string)$product->id)
            ->assertCanSeeTableRecords([$productItem])
            ->assertCanNotSeeTableRecords([$otherItem])
            ->searchTable('c-qq')
            ->assertCanSeeTableRecords([$productItem])
            ->assertCanNotSeeTableRecords([$otherItem]);
    }

    public function test_order_items_can_be_filtered_by_status_order_phone_stock_and_dates(): void
    {
        $order = $this->createOrder('+375291112233');
        $otherOrder = $this->createOrder('+375297778899');
        $stock = Stock::factory()->create();

        $completed = $this->createOrderItem($order, OrderItemStatus::COMPLETED);
        $this->createStatusLog($completed, $stock, ['completed_at' => '2026-07-10 12:00:00']);
        $canceled = $this->createOrderItem($order, OrderItemStatus::CANCELED);
        $otherOrderItem = $this->createOrderItem($otherOrder);

        Livewire::test(ListOrderItems::class)
            ->filterTable('status', [OrderItemStatus::COMPLETED->value])
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$canceled, $otherOrderItem])
            ->resetTableFilters()
            ->filterTable('order', ['order_id' => $order->id])
            ->assertCanSeeTableRecords([$completed, $canceled])
            ->assertCanNotSeeTableRecords([$otherOrderItem])
            ->resetTableFilters()
            ->filterTable('order', ['phone' => '+375 29 777-88-99'])
            ->assertCanSeeTableRecords([$otherOrderItem])
            ->assertCanNotSeeTableRecords([$completed, $canceled])
            ->resetTableFilters()
            ->filterTable('stock', [$stock->id])
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$canceled, $otherOrderItem])
            ->resetTableFilters()
            ->filterTable('completed_at', ['from' => '2026-07-10', 'until' => '2026-07-10'])
            ->assertCanSeeTableRecords([$completed])
            ->assertCanNotSeeTableRecords([$canceled, $otherOrderItem]);
    }

    public function test_excel_export_contains_filtered_items(): void
    {
        Excel::fake();

        $order = $this->createOrder();
        $completed = $this->createOrderItem($order, OrderItemStatus::COMPLETED);
        $canceled = $this->createOrderItem($order, OrderItemStatus::CANCELED);

        Livewire::test(ListOrderItems::class)
            ->filterTable('status', [OrderItemStatus::COMPLETED->value])
            ->callAction('export');

        Excel::assertDownloaded('Товары в заказах.xlsx', function (OrderItemsExport $export) use ($completed, $canceled): bool {
            $ids = $export->query()->pluck('id')->all();

            return $ids === [$completed->id]
                && !in_array($canceled->id, $ids, true)
                && $export->map($completed->fresh())[5] === 'Выкуплен';
        });
    }

    public function test_excel_export_is_refused_when_too_many_items_are_found(): void
    {
        Excel::shouldReceive('download')->never();

        $item = $this->createOrderItem($this->createOrder());
        $row = $item->only(['order_id', 'product_id', 'size_id', 'count', 'buy_price', 'price', 'old_price', 'current_price']);
        $row['status'] = OrderItemStatus::NEW->value;

        foreach (array_chunk(array_fill(0, ListOrderItems::MAX_EXPORT_ROWS, $row), 1000) as $rows) {
            OrderItem::query()->insert($rows);
        }

        Livewire::test(ListOrderItems::class)
            ->callAction('export')
            ->assertNotified('Слишком много позиций для выгрузки');
    }

    private function createOrder(string $phone = '+375291112233'): Order
    {
        return Order::withoutEvents(fn (): Order => Order::query()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString($phone),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
        ]));
    }

    private function createOrderItem(
        Order $order,
        OrderItemStatus $status = OrderItemStatus::NEW,
        ?Product $product = null,
        ?Size $size = null,
    ): OrderItem {
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
            'status' => $status,
        ]));
    }

    /**
     * @param  array<string, mixed>  $dates
     */
    private function createStatusLog(OrderItem $orderItem, Stock $stock, array $dates = []): OrderItemStatusLog
    {
        return OrderItemStatusLog::query()->create([
            'order_item_id' => $orderItem->id,
            'stock_id' => $stock->id,
            'created_at' => now(),
            ...$dates,
        ]);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'order_items_admin',
            'password' => bcrypt('secret'),
            'name' => 'Order Items',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
