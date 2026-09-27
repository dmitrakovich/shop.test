<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use App\Models\Category;
use App\Models\Logs\SearchQueryLog;
use App\Models\Product;
use App\Models\User\Device as UserDevice;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_it_returns_text_suggestions_and_matching_categories(): void
    {
        $this->mockSuggestionSearch();

        $response = $this->getJson('/api/v1/search/suggestions?query=' . rawurlencode('бот'), [
            'device-id' => self::DEVICE_ID,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('suggestions.0', 'ботинки')
            ->assertJsonPath('suggestions.1', 'ботинки черные')
            ->assertJsonPath('queries', [])
            ->assertJsonPath('products', []);

        $names = array_column($response->json('categories'), 'name');
        $this->assertSame('Ботинки', $names[0]);
        $this->assertContains('Ботильоны', $names);
        $this->assertNotContains('Каталог', $names);
    }

    public function test_short_query_returns_popular_products_and_top_queries(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldNotReceive('search');
        $this->app->instance(DocumentManager::class, $documents);

        $categoryId = Category::query()->whereKeyNot(Category::ROOT_CATEGORY_ID)->value('id');
        $popular = Product::factory()->published()->create([
            'category_id' => $categoryId,
            'rating' => 100,
        ]);
        Product::factory()->published()->create([
            'category_id' => $categoryId,
            'rating' => 10,
        ]);
        $unpublished = Product::factory()->unpublished()->create([
            'category_id' => $categoryId,
            'rating' => 999,
            'deleted_at' => now(),
        ]);

        $first = UserDevice::query()->create(['api_id' => (string)Str::uuid()]);
        $second = UserDevice::query()->create(['api_id' => (string)Str::uuid()]);

        $this->logSearch('ботинки', $first);
        $this->logSearch('ботинки', $second);
        $this->logSearch('сумка', $first);
        $this->logSearch('сумка', $first);
        $this->logSearch('сумка', $first);
        $this->logSearch('кеды', $first, resultsCount: 0);
        $this->logSearch('кеды', $second, resultsCount: 0);
        $this->logSearch('сапоги', $first, createdAt: now()->subDays(31));
        $this->logSearch('сапоги', $second, createdAt: now()->subDays(31));

        $response = $this->getJson('/api/v1/search/suggestions', [
            'device-id' => self::DEVICE_ID,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('suggestions', [])
            ->assertJsonPath('categories', [])
            ->assertJsonPath('queries', ['ботинки', 'сумка'])
            ->assertJsonPath('products.0.id', $popular->id);

        $this->assertNotContains($unpublished->id, array_column($response->json('products'), 'id'));
    }

    public function test_one_character_stays_on_the_empty_state(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldNotReceive('search');
        $this->app->instance(DocumentManager::class, $documents);

        $this->getJson('/api/v1/search/suggestions?query=' . rawurlencode('б'), [
            'device-id' => self::DEVICE_ID,
        ])
            ->assertOk()
            ->assertJsonPath('suggestions', [])
            ->assertJsonPath('categories', []);
    }

    public function test_query_longer_than_100_characters_is_rejected(): void
    {
        $this->getJson('/api/v1/search/suggestions?query=' . str_repeat('а', 101), [
            'device-id' => self::DEVICE_ID,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['query']);
    }

    private function logSearch(
        string $query,
        UserDevice $device,
        int $resultsCount = 5,
        ?Carbon $createdAt = null,
    ): void {
        $log = SearchQueryLog::query()->create([
            'device_id' => $device->id,
            'query' => $query,
            'results_count' => $resultsCount,
        ]);

        if ($createdAt !== null) {
            $log->created_at = $createdAt;
            $log->save();
        }
    }

    private function mockSuggestionSearch(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->once()
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => 0],
                    'hits' => [],
                ],
                'suggest' => [
                    'phrases' => [[
                        'text' => 'бот',
                        'offset' => 0,
                        'length' => 3,
                        'options' => [
                            ['text' => 'ботинки', '_source' => ['text' => 'ботинки']],
                            ['text' => 'ботинки черные', '_source' => ['text' => 'ботинки черные']],
                        ],
                    ]],
                ],
            ]));
        $this->app->instance(DocumentManager::class, $documents);
    }
}
