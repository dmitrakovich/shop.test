<?php

namespace Tests\Feature\Filament\Products;

use App\Filament\Resources\Products\Products\Pages\EditProduct;
use App\Jobs\Elasticsearch\UpsertCatalogProductJob;
use App\Models\Admin\AdminUser;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_removing_do_not_publish_label_publishes_and_reindexes_product(): void
    {
        Bus::fake([UpsertCatalogProductJob::class]);

        $product = Product::factory()->unpublished()->create([
            ...$this->createRequiredProductRelations(),
            'deleted_at' => now(),
        ]);
        $product->sizes()->attach(Size::factory()->create());

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm(['label_id' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();

        $this->assertNull($product->label_id);
        $this->assertNull($product->deleted_at);
        Bus::assertDispatched(
            UpsertCatalogProductJob::class,
            fn (UpsertCatalogProductJob $job): bool => $job->productId === $product->id,
        );
    }

    public function test_editing_product_without_do_not_publish_label_does_not_restore_it(): void
    {
        Bus::fake([UpsertCatalogProductJob::class]);

        $product = Product::factory()->published()->create([
            ...$this->createRequiredProductRelations(),
            'deleted_at' => now(),
        ]);
        $product->sizes()->attach(Size::factory()->create());

        $this->actingAs($this->createSuperAdmin(), 'admin');

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->fillForm(['description' => 'Updated description'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();

        $this->assertStringContainsString('Updated description', $product->description);
        $this->assertNotNull($product->deleted_at);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => fake()->unique()->userName(),
            'password' => bcrypt('secret'),
            'name' => 'Product Publication Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }

    /**
     * @return array{category_id: int, brand_id: int, collection_id: int, season_id: int}
     */
    private function createRequiredProductRelations(): array
    {
        $categoryId = DB::table('categories')->value('id') ?? DB::table('categories')->insertGetId([
            'slug' => 'test-category',
            'path' => 'test-category',
            'title' => 'Test category',
        ]);
        $brandId = DB::table('brands')->value('id') ?? DB::table('brands')->insertGetId([
            'name' => 'Test brand',
            'slug' => 'test-brand',
        ]);
        $collectionId = DB::table('collections')->value('id') ?? DB::table('collections')->insertGetId([
            'name' => 'Test collection',
            'slug' => 'test-collection',
        ]);
        $seasonId = DB::table('seasons')->value('id') ?? DB::table('seasons')->insertGetId([
            'name' => 'Test season',
            'slug' => 'test-season',
        ]);

        return [
            'category_id' => (int)$categoryId,
            'brand_id' => (int)$brandId,
            'collection_id' => (int)$collectionId,
            'season_id' => (int)$seasonId,
        ];
    }
}
