<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
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
            ->assertJsonMissingPath('sortingList');

        $this->assertSame(
            ['products', 'banners', 'category', 'facets', 'sort', 'meta'],
            array_keys($response->json()),
        );
    }
}
