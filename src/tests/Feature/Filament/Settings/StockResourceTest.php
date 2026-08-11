<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Resources\Settings\Stocks\Pages\EditStock;
use App\Filament\Resources\Settings\Stocks\Pages\ListStocks;
use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Models\Admin\AdminUser;
use App\Models\Stock;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StockResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::query()->create([
            'username' => 'stock_admin',
            'password' => bcrypt('secret'),
            'name' => 'Stock Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $this->admin->assignRole($role);
    }

    public function test_stocks_list_page_can_be_rendered(): void
    {
        $stock = Stock::factory()->create();

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ListStocks::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords([$stock]);
    }

    public function test_stock_can_be_deactivated_from_list_with_catalog_sync(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $stock = Stock::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(ListStocks::class)
            ->callAction(TestAction::make('toggleActive')->table($stock))
            ->assertNotified();

        $this->assertFalse($stock->fresh()->is_active);
        Bus::assertDispatchedSync(UpdateAvailabilityJob::class);
    }

    public function test_stock_can_be_activated_from_edit_page(): void
    {
        Bus::fake([UpdateAvailabilityJob::class]);

        $stock = Stock::factory()->inactive()->create();

        $this->actingAs($this->admin, 'admin');

        Livewire::test(EditStock::class, ['record' => $stock->getKey()])
            ->callAction('toggleActive')
            ->assertNotified();

        $this->assertTrue($stock->fresh()->is_active);
        Bus::assertDispatchedSync(UpdateAvailabilityJob::class);
    }
}
