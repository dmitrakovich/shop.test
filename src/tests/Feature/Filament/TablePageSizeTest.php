<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Inventory\InventoryLogs\Pages\ListInventoryLogs;
use App\Filament\Resources\Orders\OfflineOrders\Pages\ListOfflineOrders;
use App\Filament\Resources\Orders\OrderItems\Pages\ListOrderItems;
use App\Filament\Resources\Orders\OrderItemStatusLogs\Pages\ListOrderItemStatusLogs;
use App\Filament\Resources\Seo\Cities\Pages\ListCities;
use App\Filament\Resources\Seo\SeoPages\Pages\ListSeoPages;
use App\Filament\Resources\Users\Users\Pages\ListUsers;
use App\Models\Admin\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Large tables open with the panel default page size to keep the first render fast.
 */
class TablePageSizeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{class-string}>
     */
    public static function listPages(): array
    {
        return [
            'inventory log' => [ListInventoryLogs::class],
            'item movements' => [ListOrderItemStatusLogs::class],
            'offline sales' => [ListOfflineOrders::class],
            'order items' => [ListOrderItems::class],
            'users' => [ListUsers::class],
            'cities' => [ListCities::class],
            'seo pages' => [ListSeoPages::class],
        ];
    }

    #[DataProvider('listPages')]
    public function test_table_opens_with_panel_default_page_size(string $page): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test($page)
            ->assertSuccessful()
            ->assertSet('tableRecordsPerPage', 10);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'page_size_admin',
            'password' => bcrypt('secret'),
            'name' => 'Page Size',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
