<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use App\Models\Category;
use App\Models\Product;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class V2CatalogContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_it_returns_the_minimal_v2_contract_without_legacy_fields(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->once()
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => 0],
                    'hits' => [],
                ],
                'aggregations' => [
                    'min_price' => ['value' => ['value' => 0]],
                    'max_price' => ['value' => ['value' => 0]],
                ],
            ]));
        $this->app->instance(DocumentManager::class, $documents);

        $response = $this->getJson('/api/v2/catalog', [
            'device-id' => '8d854825-6753-4a16-9056-9f36b7ac7b90',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'products',
                'banners',
                'category' => [
                    'id',
                    'name',
                    'slug',
                    'path',
                    'parent_category',
                ],
                'facets',
                'sort' => ['value', 'options'],
                'meta',
            ])
            ->assertJsonMissingPath('currentFilters')
            ->assertJsonMissingPath('searchQuery')
            ->assertJsonMissingPath('badges')
            ->assertJsonMissingPath('filters')
            ->assertJsonMissingPath('sortingList')
            ->assertJsonPath('products.data', [])
            ->assertJsonPath('products.total', 0);

        $this->assertSame(
            ['products', 'banners', 'category', 'facets', 'sort', 'meta'],
            array_keys($response->json()),
        );
    }

    public function test_catalog_products_omit_internal_fields(): void
    {
        $categoryId = Category::query()->whereKeyNot(Category::ROOT_CATEGORY_ID)->value('id');
        $product = Product::factory()->published()->create([
            'category_id' => $categoryId,
            'buy_price' => 1234.56,
            'one_c_id' => 987654321,
        ]);

        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->once()
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => 1],
                    'hits' => [
                        ['_id' => (string)$product->id],
                    ],
                ],
                'aggregations' => [
                    'min_price' => ['value' => ['value' => 10]],
                    'max_price' => ['value' => ['value' => 100]],
                ],
            ]));
        $this->app->instance(DocumentManager::class, $documents);

        $response = $this->getJson('/api/v2/catalog', [
            'device-id' => '8d854825-6753-4a16-9056-9f36b7ac7b90',
        ]);

        $response->assertOk()
            ->assertJsonPath('products.data.0.id', $product->id)
            ->assertJsonPath('products.data.0.slug', $product->slug)
            ->assertJsonPath('products.total', 1);

        $productJson = $response->json('products.data.0');
        $this->assertIsArray($productJson);
        $this->assertArrayNotHasKey('buy_price', $productJson);
        $this->assertArrayNotHasKey('one_c_id', $productJson);

        $encoded = json_encode($productJson, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('"buy_price"', $encoded);
        $this->assertStringNotContainsString('"one_c_id"', $encoded);
        $this->assertStringNotContainsString('987654321', $encoded);
    }
}
