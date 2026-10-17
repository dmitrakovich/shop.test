<?php

namespace Tests\Feature\Api;

use App\Facades\Device;
use App\Models\Category;
use App\Models\Logs\SearchQueryLog;
use App\Models\Product;
use App\Models\User\Device as UserDevice;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchParameters;
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

    public function test_it_returns_text_suggestions_matching_categories_and_products(): void
    {
        $first = $this->createProduct();
        $second = $this->createProduct();
        $this->mockSuggestionSearch(static fn (string $query): array => [$second->id, $first->id]);

        $response = $this->getJson('/api/v1/search/suggestions?query=' . rawurlencode('бот'), [
            'device-id' => self::DEVICE_ID,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('suggestions.0', 'ботинки')
            ->assertJsonPath('suggestions.1', 'ботинки черные')
            ->assertJsonPath('queries', []);

        $this->assertSame([$second->id, $first->id], array_column($response->json('products'), 'id'));

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

    public function test_products_are_retried_in_the_other_keyboard_layout(): void
    {
        $product = $this->createProduct();
        $catalogQueries = [];
        $this->mockSuggestionSearch(static function (string $query) use ($product, &$catalogQueries): array {
            $catalogQueries[] = $query;

            return $query === 'ботинки' ? [$product->id] : [];
        });

        $this->getJson('/api/v1/search/suggestions?query=' . rawurlencode(',jnbyrb'), [
            'device-id' => self::DEVICE_ID,
        ])
            ->assertOk()
            ->assertJsonPath('products.0.id', $product->id);

        $this->assertSame([',jnbyrb', 'ботинки'], $catalogQueries);
    }

    public function test_no_matching_products_returns_an_empty_list(): void
    {
        $this->mockSuggestionSearch(static fn (string $query): array => []);

        $this->getJson('/api/v1/search/suggestions?query=' . rawurlencode('юбка'), [
            'device-id' => self::DEVICE_ID,
        ])
            ->assertOk()
            ->assertJsonPath('products', []);
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

    private function createProduct(): Product
    {
        return Product::factory()->published()->create([
            'category_id' => Category::query()->whereKeyNot(Category::ROOT_CATEGORY_ID)->value('id'),
        ]);
    }

    /**
     * Phrase suggestions come from the suggestions index; catalog hits are resolved per searched word.
     *
     * @param  callable(string): list<int>  $catalogHits
     */
    private function mockSuggestionSearch(callable $catalogHits): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->andReturnUsing(static function (SearchParameters $parameters) use ($catalogHits): SearchResult {
                $request = $parameters->toArray();

                if ($request['index'] === 'catalog_suggestions') {
                    return new SearchResult([
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
                    ]);
                }

                $productIds = $catalogHits($request['body']['query']['bool']['should'][0]['multi_match']['query']);

                return new SearchResult([
                    'hits' => [
                        'total' => ['value' => count($productIds)],
                        'hits' => array_map(static fn (int $id): array => [
                            '_index' => 'catalog_v1',
                            '_id' => (string)$id,
                            '_source' => [],
                        ], $productIds),
                    ],
                ]);
            });
        $this->app->instance(DocumentManager::class, $documents);
    }
}
