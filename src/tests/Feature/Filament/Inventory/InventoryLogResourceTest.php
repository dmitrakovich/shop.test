<?php

namespace Tests\Feature\Filament\Inventory;

use App\Enums\Product\InventoryLogAction;
use App\Filament\Resources\Inventory\InventoryLogs\InventoryLogResource;
use App\Filament\Resources\Inventory\InventoryLogs\Pages\ListInventoryLogs;
use App\Filament\Resources\Products\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Products\RelationManagers\InventoryLogsRelationManager;
use App\Models\Admin\AdminUser;
use App\Models\Logs\InventoryLog;
use App\Models\Product;
use App\Models\Size;
use App\Services\LogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryLogResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->createSuperAdmin(), 'admin');
    }

    public function test_inventory_log_page_shows_actions_and_size_names(): void
    {
        $addedSize = Size::factory()->create(['name' => '38.5']);
        $removedSize = Size::factory()->create(['name' => '41.5']);
        $product = Product::factory()->create(['sku' => 'SKU-LOG-1']);

        $restored = InventoryLog::factory()->restored()->for($product)->create([
            'added_sizes' => [$addedSize->id],
        ]);
        $deleted = InventoryLog::factory()->deleted()->for($product)->create([
            'removed_sizes' => [$removedSize->id],
        ]);

        $this->get(InventoryLogResource::getUrl())->assertOk();

        Livewire::test(ListInventoryLogs::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$restored, $deleted])
            ->assertSee('Опубликован')
            ->assertSee('Снят с публикации')
            ->assertSee('SKU-LOG-1')
            ->assertSee('38.5')
            ->assertSee('41.5');
    }

    public function test_inventory_log_can_be_searched_by_product_id_and_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'ABC-QQQ']);
        $otherProduct = Product::factory()->create(['sku' => 'XYZ-WWW']);

        $productLog = InventoryLog::factory()->for($product)->create();
        $otherLog = InventoryLog::factory()->for($otherProduct)->create();

        Livewire::test(ListInventoryLogs::class)
            ->searchTable((string)$product->id)
            ->assertCanSeeTableRecords([$productLog])
            ->assertCanNotSeeTableRecords([$otherLog])
            ->searchTable('c-qq')
            ->assertCanSeeTableRecords([$productLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    public function test_inventory_log_can_be_filtered_by_action(): void
    {
        $product = Product::factory()->create();

        $updated = InventoryLog::factory()->for($product)->create();
        $deleted = InventoryLog::factory()->deleted()->for($product)->create();

        Livewire::test(ListInventoryLogs::class)
            ->filterTable('action', InventoryLogAction::Delete->value)
            ->assertCanSeeTableRecords([$deleted])
            ->assertCanNotSeeTableRecords([$updated]);
    }

    public function test_inventory_log_shows_entries_of_deleted_products(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-TRASHED']);
        $product->delete();

        $log = InventoryLog::factory()->deleted()->for($product)->create();

        $removedProduct = Product::factory()->create();
        $removedProductLog = InventoryLog::factory()->deleted()->for($removedProduct)->create();
        $removedProduct->forceDelete();

        Livewire::test(ListInventoryLogs::class)
            ->assertCanSeeTableRecords([$log, $removedProductLog])
            ->assertSee('SKU-TRASHED')
            ->assertTableColumnFormattedStateSet('product_id', 'Товар удалён', $removedProductLog);
    }

    public function test_product_page_shows_only_its_inventory_history(): void
    {
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();

        $productLog = InventoryLog::factory()->restored()->for($product)->create();
        $otherLog = InventoryLog::factory()->for($otherProduct)->create();

        Livewire::test(InventoryLogsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$productLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    public function test_log_service_stores_actions_readable_as_enum(): void
    {
        $restoredProduct = Product::factory()->create();
        $deletedProduct = Product::factory()->create();
        $updatedProduct = Product::factory()->create();

        app(LogService::class)->logAvailabilityUpdate(
            [$restoredProduct->id],
            [$deletedProduct->id],
            [$updatedProduct->id => [5, 7]],
            [],
        );

        $actions = InventoryLog::query()->pluck('action', 'product_id');

        $this->assertSame(InventoryLogAction::Restore, $actions[$restoredProduct->id]);
        $this->assertSame(InventoryLogAction::Delete, $actions[$deletedProduct->id]);
        $this->assertSame(InventoryLogAction::Update, $actions[$updatedProduct->id]);
        $this->assertSame(
            [5, 7],
            InventoryLog::query()->where('product_id', $updatedProduct->id)->firstOrFail()->added_sizes,
        );
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'inventory_log_admin',
            'password' => bcrypt('secret'),
            'name' => 'Inventory Log',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
