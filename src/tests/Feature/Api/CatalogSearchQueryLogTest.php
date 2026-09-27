<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use App\Models\Logs\SearchQueryLog;
use App\Models\User\Device as UserDevice;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class CatalogSearchQueryLogTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
        $this->mockCatalogSearch(4);
    }

    public function test_it_logs_the_first_page_of_a_search(): void
    {
        $this->getJson('/api/v2/catalog/catalog?search=' . rawurlencode('  Ботинки   Черные '), [
            'device-id' => self::DEVICE_ID,
        ])->assertOk();

        $device = UserDevice::query()->where('api_id', self::DEVICE_ID)->firstOrFail();

        $this->assertDatabaseHas('log_search_queries', [
            'device_id' => $device->id,
            'query' => 'ботинки черные',
            'results_count' => 4,
            'filters_path' => 'catalog',
        ]);
        $this->assertSame(1, SearchQueryLog::query()->count());
    }

    public function test_it_does_not_log_browsing_later_pages_or_noise_only_queries(): void
    {
        $this->getCatalog('/api/v2/catalog');
        $this->getCatalog('/api/v2/catalog?search=' . rawurlencode('ботинки') . '&page=2');
        $this->getCatalog('/api/v2/catalog?search=' . rawurlencode('размер'));

        $this->assertSame(0, SearchQueryLog::query()->count());
    }

    private function getCatalog(string $uri): void
    {
        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);

        $this->getJson($uri, [
            'device-id' => self::DEVICE_ID,
        ])->assertOk();
    }

    private function mockCatalogSearch(int $total): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => $total],
                    'hits' => [],
                ],
                'aggregations' => [
                    'min_price' => ['value' => ['value' => 0]],
                    'max_price' => ['value' => ['value' => 0]],
                ],
            ]));
        $this->app->instance(DocumentManager::class, $documents);
    }
}
