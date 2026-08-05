<?php

namespace Tests\Unit\Elastic;

use App\Enums\Product\ProductSort;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductAttributes\Price;
use App\Models\ProductAttributes\Status;
use App\Models\Size;
use App\Models\Url;
use App\Services\Elasticsearch\CatalogSearchService;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchResult;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CatalogSearchServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private CatalogSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['catalog.elasticsearch.alias' => 'catalog']);
        $this->service = new CatalogSearchService(
            Mockery::mock(DocumentManager::class),
        );
    }

    public function test_attribute_filters_are_and_across_groups_or_within(): void
    {
        $groups = $this->service->buildFilterGroups([
            Brand::class => [
                'nike' => $this->urlWithModelId(5),
                'adidas' => $this->urlWithModelId(7),
            ],
            Size::class => [
                '38' => $this->urlWithModelId(101),
            ],
        ]);

        $this->assertSame([['terms' => ['brand.id' => [5, 7]]]], $groups[Brand::class]);
        $this->assertSame([['term' => ['sizes.id' => 101]]], $groups[Size::class]);
    }

    public function test_category_uses_last_segment_on_categories_id(): void
    {
        $groups = $this->service->buildFilterGroups([
            Category::class => [
                'root' => $this->urlWithModelId(1),
                'shoes' => $this->urlWithModelId(10),
            ],
        ]);

        $this->assertSame([['term' => ['categories.id' => 10]]], $groups[Category::class]);
    }

    public function test_root_category_adds_no_clause(): void
    {
        $groups = $this->service->buildFilterGroups([
            Category::class => [
                'catalog' => $this->urlWithModelId(Category::ROOT_CATEGORY_ID),
            ],
        ]);

        $this->assertSame([], $groups);
    }

    public function test_status_slugs_are_or_within_group(): void
    {
        $groups = $this->service->buildFilterGroups([
            Status::class => [
                'st-new' => $this->urlWithModelId(1),
                'st-sale' => $this->urlWithModelId(2),
            ],
        ]);

        $this->assertSame([
            ['terms' => ['status_slugs' => ['st-new', 'st-sale']]],
        ], $groups[Status::class]);
    }

    public function test_price_is_built_as_its_own_filter_group(): void
    {
        $from = new Price();
        $from->slug = 'price-from-100';
        $to = new Price();
        $to->slug = 'price-to-500';

        $groups = $this->service->buildFilterGroups([
            Price::class => [
                'price-from-100' => $this->urlWithFilter($from, 0),
                'price-to-500' => $this->urlWithFilter($to, 0),
            ],
        ]);

        $this->assertSame([
            [
                'range' => [
                    'price' => [
                        'gt' => 100.0,
                        'lt' => 500.0,
                    ],
                ],
            ],
        ], $groups[Price::class]);
    }

    public function test_search_sort_uses_relevance_score(): void
    {
        $sort = $this->service->buildSort(ProductSort::Newness, [], 'туфли');

        $this->assertSame([
            ['_score' => ['order' => 'desc']],
            ['id' => ['order' => 'desc']],
        ], $sort);
    }

    public function test_rating_sort_uses_sale_rating_for_st_sale(): void
    {
        $sort = $this->service->buildSort(ProductSort::Rating, [
            Status::class => ['st-sale' => $this->urlWithModelId(1)],
        ], null);

        $this->assertSame('sale_rating', array_key_first($sort[0]));
    }

    public function test_promotion_status_is_ignored(): void
    {
        $this->assertArrayNotHasKey(
            Status::class,
            $this->service->buildFilterGroups([
                Status::class => ['promotion' => $this->urlWithModelId(1)],
            ]),
        );

        $groups = $this->service->buildFilterGroups([
            Status::class => [
                'promotion' => $this->urlWithModelId(1),
                'st-sale' => $this->urlWithModelId(2),
            ],
        ]);

        $this->assertSame([
            ['term' => ['status_slugs' => 'st-sale']],
        ], $groups[Status::class]);
    }

    public function test_build_search_parameters_wires_post_filter_and_aggs(): void
    {
        $from = new Price();
        $from->slug = 'price-from-50';

        $parameters = $this->service->buildSearchParameters(
            [
                Brand::class => ['x' => $this->urlWithModelId(3)],
                Price::class => ['price-from-50' => $this->urlWithFilter($from, 0)],
            ],
            ProductSort::PriceDown,
            null,
            2,
            24,
        )->toArray();

        $this->assertSame('catalog', $parameters['index']);
        $this->assertSame(24, $parameters['body']['from']);
        $this->assertSame(24, $parameters['body']['size']);
        $this->assertEquals(['match_all' => (object)[]], $parameters['body']['query']);
        $this->assertArrayHasKey('post_filter', $parameters['body']);
        $this->assertArrayHasKey('min_price', $parameters['body']['aggregations']);
        $this->assertArrayHasKey('max_price', $parameters['body']['aggregations']);
        $this->assertContains(
            ['range' => ['price' => ['gt' => 50.0]]],
            $parameters['body']['post_filter']['bool']['filter'],
        );
        $this->assertContains(
            ['term' => ['brand.id' => 3]],
            $parameters['body']['aggregations']['min_price']['filter']['bool']['filter'],
        );
        $this->assertNotContains(
            ['range' => ['price' => ['gt' => 50.0]]],
            $parameters['body']['aggregations']['min_price']['filter']['bool']['filter'],
        );
        $this->assertFalse($parameters['body']['_source']);
    }

    public function test_facet_aggregations_exclude_their_own_filter_group(): void
    {
        $parameters = $this->service->buildSearchParameters(
            [
                Brand::class => ['nike' => $this->urlWithModelId(3)],
                Size::class => ['38' => $this->urlWithModelId(38)],
            ],
            ProductSort::Newness,
            null,
            1,
            12,
        )->toArray();

        $aggregations = $parameters['body']['aggregations'];

        $this->assertContains(
            ['term' => ['sizes.id' => 38]],
            $aggregations['facet_brands']['filter']['bool']['filter'],
        );
        $this->assertNotContains(
            ['term' => ['brand.id' => 3]],
            $aggregations['facet_brands']['filter']['bool']['filter'],
        );
        $this->assertContains(
            ['term' => ['brand.id' => 3]],
            $aggregations['facet_sizes']['filter']['bool']['filter'],
        );
        $this->assertNotContains(
            ['term' => ['sizes.id' => 38]],
            $aggregations['facet_sizes']['filter']['bool']['filter'],
        );
        $this->assertSame(
            ['terms' => ['field' => 'brand.id', 'size' => 1000]],
            $aggregations['facet_brands']['aggs']['values'],
        );
        $this->assertEquals(
            ['match_all' => (object)[]],
            $parameters['body']['query'],
        );
    }

    public function test_facet_aggregations_include_selected_price_range(): void
    {
        $from = new Price();
        $from->slug = 'price-from-100';

        $parameters = $this->service->buildSearchParameters(
            [
                Brand::class => ['nike' => $this->urlWithModelId(3)],
                Price::class => ['price-from-100' => $this->urlWithFilter($from, 0)],
            ],
            ProductSort::Newness,
            null,
            1,
            12,
        )->toArray();

        $brandFilters = $parameters['body']['aggregations']['facet_brands']['filter']['bool']['filter'];

        $this->assertContains(['range' => ['price' => ['gt' => 100.0]]], $brandFilters);
        $this->assertNotContains(['term' => ['brand.id' => 3]], $brandFilters);
    }

    public function test_search_parses_facet_aggregation_buckets(): void
    {
        $documents = Mockery::mock(DocumentManager::class);
        $documents->shouldReceive('search')
            ->once()
            ->andReturn(new SearchResult([
                'hits' => [
                    'total' => ['value' => 2],
                    'hits' => [],
                ],
                'aggregations' => [
                    'min_price' => ['value' => ['value' => 10]],
                    'max_price' => ['value' => ['value' => 100]],
                    'facet_brands' => [
                        'values' => [
                            'buckets' => [
                                ['key' => 3, 'doc_count' => 2],
                            ],
                        ],
                    ],
                    'facet_statuses' => [
                        'values' => [
                            'buckets' => [
                                ['key' => 'st-new', 'doc_count' => 1],
                            ],
                        ],
                    ],
                ],
            ]));
        $service = new CatalogSearchService($documents);

        $result = $service->search([], ProductSort::Newness, null, 1, 12);

        $this->assertSame(['3' => 2], $result->facetCounts['brands']);
        $this->assertSame(['st-new' => 1], $result->facetCounts['statuses']);
        $this->assertSame(10.0, $result->minPrice);
        $this->assertSame(100.0, $result->maxPrice);
    }

    public function test_normalize_search_strips_size_filler_words(): void
    {
        $this->assertSame('туфли 38', $this->service->normalizeSearchQuery('туфли размера 38'));
        $this->assertSame('38', $this->service->normalizeSearchQuery('размер 38'));
        $this->assertSame('', $this->service->normalizeSearchQuery('размера'));
    }

    public function test_normalize_search_strips_color_filler_words(): void
    {
        $this->assertSame('сумка черный', $this->service->normalizeSearchQuery('сумка цвета черный'));
        $this->assertSame('бордовый', $this->service->normalizeSearchQuery('цвет бордовый'));
    }

    public function test_search_clause_after_noise_strip_uses_simple_numeric_path(): void
    {
        $clause = $this->service->buildSearchClause('размера 38');

        $this->assertIsArray($clause);
        $this->assertArrayHasKey('bool', $clause);
        $this->assertContains(['term' => ['id' => 38]], $clause['bool']['should']);
    }

    public function test_text_search_clause_uses_per_word_fuzziness_and_msm(): void
    {
        $clause = $this->service->buildSearchClause('красные туфли');

        $this->assertSame(2, $clause['bool']['minimum_should_match']);
        $this->assertCount(2, $clause['bool']['should']);
        $this->assertSame('AUTO', $clause['bool']['should'][0]['multi_match']['fuzziness']);
        $this->assertSame([
            'sku.text^12',
            'brand.name^7',
            'short_name^6',
            'categories.name^5',
            'color_txt',
            'sizes.name',
            'colors.name',
            'tags.name',
        ], $clause['bool']['should'][0]['multi_match']['fields']);
    }

    public function test_three_word_search_allows_one_miss(): void
    {
        $clause = $this->service->buildSearchClause('красные кожаные туфли');

        $this->assertSame(2, $clause['bool']['minimum_should_match']);
        $this->assertCount(3, $clause['bool']['should']);
    }

    private function urlWithModelId(int $modelId): Url
    {
        $url = new Url();
        $url->model_id = $modelId;

        return $url;
    }

    private function urlWithFilter(object $filter, int $modelId): Url
    {
        $url = $this->urlWithModelId($modelId);
        $url->setRelation('filters', $filter);

        return $url;
    }
}
