<?php

namespace Tests\Feature\Filament\Orders;

use App\Filament\Resources\Orders\OfflineOrders\OfflineOrderResource;
use App\Filament\Resources\Orders\OfflineOrders\Pages\ListOfflineOrders;
use App\Filament\Resources\Users\Users\Pages\EditUser;
use App\Filament\Resources\Users\Users\RelationManagers\OfflineOrdersRelationManager;
use App\Filament\Resources\Users\Users\UserResource;
use App\Models\Admin\AdminUser;
use App\Models\Orders\OfflineOrder;
use App\Models\Product;
use App\Models\Size;
use App\Models\Stock;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OfflineOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    private Stock $stock;

    private int $oneCId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'admin');
        $this->stock = Stock::factory()->create(['internal_name' => 'МИНСК Тест']);
    }

    public function test_offline_sales_page_shows_sale_product_client_and_return(): void
    {
        $user = $this->createUser('+375291112233');
        $product = Product::factory()->create();
        $size = Size::factory()->create(['name' => '37.5']);

        $sale = $this->createOfflineOrder([
            'product_id' => $product->id,
            'sku' => 'G5108-16A',
            'size_id' => $size->id,
            'price' => 365.25,
            'user_id' => $user->id,
            'user_phone' => '375291112233',
            'returned_at' => now(),
        ]);
        $unknownProductSale = $this->createOfflineOrder(['sku' => 'ONLY-IN-1C']);

        $this->get(OfflineOrderResource::getUrl())->assertOk();

        Livewire::test(ListOfflineOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$sale, $unknownProductSale])
            ->assertSee('G5108-16A')
            ->assertSee("код {$product->id}")
            ->assertSee('ONLY-IN-1C')
            ->assertSee('нет на сайте')
            ->assertSee('37.5')
            ->assertSee('365,25')
            ->assertSee('МИНСК Тест')
            ->assertSee('Иванова Анна')
            ->assertSee('+375291112233');
    }

    public function test_missing_phone_from_1c_is_shown_as_empty(): void
    {
        $noneSale = $this->createOfflineOrder(['user_phone' => 'NONE']);
        $emptySale = $this->createOfflineOrder(['user_phone' => '']);

        Livewire::test(ListOfflineOrders::class)
            ->assertTableColumnStateNotSet('user_phone', 'NONE', $noneSale)
            ->assertTableColumnStateSet('user_phone', null, $noneSale)
            ->assertTableColumnStateSet('user_phone', null, $emptySale)
            ->assertDontSee('NONE')
            ->filterTable('user_phone', ['user_phone' => 'NONE'])
            ->assertCanNotSeeTableRecords([$noneSale, $emptySale]);
    }

    public function test_offline_sales_can_be_searched_by_product_id_and_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'SITE-SKU-AB']);
        $byProduct = $this->createOfflineOrder(['product_id' => $product->id, 'sku' => 'ONE-C-AAA']);
        $bySku = $this->createOfflineOrder(['sku' => 'ABC-QQQ']);
        $other = $this->createOfflineOrder(['sku' => 'XYZ-WWW']);

        Livewire::test(ListOfflineOrders::class)
            ->searchTable((string)$product->id)
            ->assertCanSeeTableRecords([$byProduct])
            ->assertCanNotSeeTableRecords([$bySku, $other])
            ->searchTable('site-sku')
            ->assertCanSeeTableRecords([$byProduct])
            ->assertCanNotSeeTableRecords([$bySku, $other])
            ->searchTable('c-qq')
            ->assertCanSeeTableRecords([$bySku])
            ->assertCanNotSeeTableRecords([$byProduct, $other]);
    }

    public function test_offline_sales_can_be_filtered_by_stock_return_and_phone(): void
    {
        $otherStock = Stock::factory()->create();

        $sale = $this->createOfflineOrder(['user_phone' => '375291112233']);
        $returned = $this->createOfflineOrder(['returned_at' => now()]);
        $otherStockSale = $this->createOfflineOrder(['stock_id' => $otherStock->id]);

        Livewire::test(ListOfflineOrders::class)
            ->filterTable('stock_id', [$otherStock->id])
            ->assertCanSeeTableRecords([$otherStockSale])
            ->assertCanNotSeeTableRecords([$sale, $returned])
            ->resetTableFilters()
            ->filterTable('returned_at', true)
            ->assertCanSeeTableRecords([$returned])
            ->assertCanNotSeeTableRecords([$sale, $otherStockSale])
            ->resetTableFilters()
            ->filterTable('user_phone', ['user_phone' => '+375 29 111-22-33'])
            ->assertCanSeeTableRecords([$sale])
            ->assertCanNotSeeTableRecords([$returned, $otherStockSale]);
    }

    public function test_client_page_shows_only_their_offline_purchases(): void
    {
        $user = $this->createUser('+375291112233');
        $otherUser = $this->createUser('+375297778899');

        $purchase = $this->createOfflineOrder(['user_id' => $user->id]);
        $otherPurchase = $this->createOfflineOrder(['user_id' => $otherUser->id]);

        $this->get(UserResource::getUrl('edit', ['record' => $user]))
            ->assertOk()
            ->assertSee('Покупки в магазинах');

        Livewire::test(OfflineOrdersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass' => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$purchase])
            ->assertCanNotSeeTableRecords([$otherPurchase])
            ->assertTableColumnHidden('user')
            ->assertTableColumnHidden('user_phone');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOfflineOrder(array $attributes = []): OfflineOrder
    {
        $oneCId = $this->oneCId++;

        return OfflineOrder::query()->create([
            'one_c_id' => $oneCId,
            'receipt_number' => sprintf('%010d', $oneCId),
            'stock_id' => $this->stock->id,
            'one_c_product_id' => $oneCId,
            'price' => 100,
            'count' => 1,
            'sku' => "SKU-{$oneCId}",
            'user_phone' => '',
            'sold_at' => now(),
            ...$attributes,
        ]);
    }

    private function createUser(string $phone): User
    {
        $group = Group::query()->firstOrCreate(['name' => 'Тест'], ['discount' => 0]);

        return User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Анна',
            'last_name' => 'Иванова',
            'phone' => Phone::fromRawString($phone),
        ]));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'offline_orders_admin',
            'password' => bcrypt('secret'),
            'name' => 'Offline Orders',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
