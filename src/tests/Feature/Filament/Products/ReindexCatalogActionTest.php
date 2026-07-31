<?php

namespace Tests\Feature\Filament\Products;

use App\Filament\Resources\Products\Products\Pages\EditProduct;
use App\Jobs\Elasticsearch\UpsertCatalogProductJob;
use App\Models\Admin\AdminUser;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReindexCatalogActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_product_can_queue_catalog_reindex(): void
    {
        Bus::fake([UpsertCatalogProductJob::class]);

        $admin = $this->createSuperAdmin();
        $product = Product::factory()->create();

        $this->actingAs($admin, 'admin');

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction('reindexCatalog')
            ->assertNotified('Товар поставлен в очередь на переиндексацию');

        Bus::assertDispatched(
            UpsertCatalogProductJob::class,
            fn (UpsertCatalogProductJob $job): bool => $job->productId === $product->id,
        );
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'product_reindex_admin',
            'password' => bcrypt('secret'),
            'name' => 'Product Reindex Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
