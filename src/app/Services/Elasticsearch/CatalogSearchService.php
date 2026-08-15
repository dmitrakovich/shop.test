<?php

namespace App\Services\Elasticsearch;

use App\Contracts\Filterable;
use App\Enums\Catalog\CatalogFacetName;
use App\Enums\Product\ProductRatingColumn;
use App\Enums\Product\ProductSort;
use App\Models\ProductAttributes\Price;
use App\Models\Url;
use App\Services\SearchService;
use Elastic\Adapter\Documents\DocumentManager;
use Elastic\Adapter\Search\SearchParameters;

class CatalogSearchService
{
    /**
     * Query tokens that are not product attributes
     * (e.g. "туфли размера 38", "сумка цвета черный").
     *
     * @var list<string>
     */
    private const SEARCH_NOISE_WORDS = [
        'размер',
        'размера',
        'размеру',
        'размером',
        'размере',
        'размеры',
        'размеров',
        'размерам',
        'размерами',
        'размерах',
        'цвет',
        'цвета',
        'цвету',
        'цветом',
        'цвете',
        'цветам',
        'цветами',
        'цветах',
        'цветов',
    ];

    /**
     * Boosted text fields for multi_match (weights aligned with
     * feature/elasticsearch-catalog-v2; object {id,name}, filters use *.id).
     *
     * @var list<string>
     */
    private const SEARCH_FIELDS = [
        'sku.text^12',
        'brand.name^7',
        'short_name^6',
        'categories.name^5',
        'color_txt',
        'sizes.name',
        'colors.name',
        'tags.name',
    ];

    public function __construct(
        private readonly DocumentManager $documents,
    ) {}

    /**
     * @param  array<string, array<array-key, Url>>  $filters
     */
    public function search(
        array $filters,
        ProductSort $sort,
        ?string $search,
        int $page,
        int $perPage,
    ): CatalogSearchResult {
        $parameters = $this->buildSearchParameters($filters, $sort, $search, $page, $perPage);
        $result = $this->documents->search($parameters);

        $productIds = $result->hits()
            ->map(static fn ($hit): int => (int)$hit->document()->id())
            ->values()
            ->all();

        $minPrice = (float)($result->aggregations()->get('min_price')?->raw()['value']['value'] ?? 0);
        $maxPrice = (float)($result->aggregations()->get('max_price')?->raw()['value']['value'] ?? 999);

        return new CatalogSearchResult(
            productIds: $productIds,
            total: (int)($result->total() ?? 0),
            minPrice: $minPrice,
            maxPrice: $maxPrice > 0 ? $maxPrice : 999,
            facetCounts: $this->parseFacetCounts($result->aggregations()->map(
                static fn ($aggregation): array => $aggregation->raw(),
            )->all()),
        );
    }

    /**
     * Build ES request parameters (unit-testable without a live cluster).
     *
     * @param  array<string, array<array-key, Url>>  $filters
     */
    public function buildSearchParameters(
        array $filters,
        ProductSort $sort,
        ?string $search,
        int $page,
        int $perPage,
    ): SearchParameters {
        $filterGroups = $this->buildFilterGroups($filters);
        $normalizedSearch = $this->normalizeSearchQuery($search);
        $searchClause = $this->buildSearchClause($search);
        $query = $searchClause ?? ['match_all' => (object)[]];

        $parameters = (new SearchParameters())
            ->indices([(string)config('catalog.elasticsearch.alias')])
            ->query($query)
            ->from(max(0, ($page - 1) * $perPage))
            ->size($perPage)
            ->trackTotalHits(true)
            ->source(false)
            ->sort($this->buildSort($sort, $filters, $normalizedSearch))
            ->aggregations([
                'min_price' => $this->buildFilteredMetricAggregation(
                    $filterGroups,
                    Price::class,
                    'min',
                ),
                'max_price' => $this->buildFilteredMetricAggregation(
                    $filterGroups,
                    Price::class,
                    'max',
                ),
                ...$this->buildFacetAggregations($filterGroups),
            ]);

        $postFilter = $this->combineFilterGroups($filterGroups);
        if ($postFilter !== null) {
            $parameters->postFilter($postFilter);
        }

        return $parameters;
    }

    /**
     * Build one disjunctive facet per filter group.
     *
     * @param  array<class-string, list<array<string, mixed>>>  $filterGroups
     * @return array<string, array<string, mixed>>
     */
    private function buildFacetAggregations(array $filterGroups): array
    {
        $aggregations = [];

        foreach (CatalogFacetName::cases() as $facet) {
            $model = $facet->model();
            if (!is_a($model, Filterable::class, true)) {
                continue;
            }

            $field = $model::elasticField();
            if ($field === null) {
                continue;
            }

            $name = $facet->value;
            $aggregations['facet_' . $name] = [
                'filter' => $this->combineFilterGroups($filterGroups, $model)
                    ?? ['match_all' => (object)[]],
                'aggs' => [
                    'values' => [
                        'terms' => [
                            'field' => $field,
                            'size' => 1000,
                        ],
                    ],
                ],
            ];
        }

        return $aggregations;
    }

    /**
     * @param  array<class-string, list<array<string, mixed>>>  $filterGroups
     * @return array<string, mixed>
     */
    private function buildFilteredMetricAggregation(
        array $filterGroups,
        string $excludedGroup,
        string $metric,
    ): array {
        return [
            'filter' => $this->combineFilterGroups($filterGroups, $excludedGroup)
                ?? ['match_all' => (object)[]],
            'aggs' => [
                'value' => [$metric => ['field' => 'price']],
            ],
        ];
    }

    /**
     * @param  array<class-string, list<array<string, mixed>>>  $filterGroups
     * @return array<string, mixed>|null
     */
    private function combineFilterGroups(array $filterGroups, ?string $excludedGroup = null): ?array
    {
        if ($excludedGroup !== null) {
            unset($filterGroups[$excludedGroup]);
        }

        $clauses = array_merge(...array_values($filterGroups));

        return $clauses === [] ? null : ['bool' => ['filter' => $clauses]];
    }

    /**
     * @param  array<string, array<string, mixed>>  $aggregations
     * @return array<string, array<string, int>>
     */
    private function parseFacetCounts(array $aggregations): array
    {
        $facetCounts = [];

        foreach (CatalogFacetName::cases() as $facet) {
            $name = $facet->value;
            $buckets = $aggregations['facet_' . $name]['values']['buckets'] ?? [];
            foreach ($buckets as $bucket) {
                $count = (int)($bucket['doc_count'] ?? 0);
                if ($count > 0 && array_key_exists('key', $bucket)) {
                    $facetCounts[$name][(string)$bucket['key']] = $count;
                }
            }
        }

        return $facetCounts;
    }

    /**
     * @param  array<string, array<array-key, Url>>  $filters
     * @return array<class-string, list<array<string, mixed>>>
     */
    public function buildFilterGroups(array $filters): array
    {
        $groups = [];

        foreach ($filters as $filterClass => $values) {
            if (!is_a($filterClass, Filterable::class, true)) {
                continue;
            }

            $clauses = $filterClass::elasticFilterClauses($values);
            if ($clauses !== []) {
                $groups[$filterClass] = $clauses;
            }
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildSearchClause(?string $search): ?array
    {
        $search = $this->normalizeSearchQuery($search);
        if ($search === '') {
            return null;
        }

        $searchService = new SearchService($search);

        if ($searchService->useSimpleSearch()) {
            $token = $searchService->getIds()[0];

            return [
                'bool' => [
                    'should' => [
                        ['term' => ['id' => (int)$token]],
                        ['wildcard' => ['sku' => ['value' => '*' . $token . '*']]],
                        ['match' => ['sizes.name' => ['query' => (string)$token, 'operator' => 'and']]],
                        ['match' => ['short_name' => ['query' => (string)$token, 'operator' => 'and']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];
        }

        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words === []) {
            return null;
        }

        $should = [];
        foreach ($words as $word) {
            $should[] = [
                'multi_match' => [
                    'query' => $word,
                    'fields' => self::SEARCH_FIELDS,
                    'type' => 'best_fields',
                    'fuzziness' => 'AUTO',
                    'prefix_length' => 1,
                    'max_expansions' => 50,
                ],
            ];
        }

        $n = count($words);

        return [
            'bool' => [
                'should' => $should,
                'minimum_should_match' => $n <= 2 ? $n : $n - 1,
            ],
        ];
    }

    /**
     * Drop filler words so "туфли размера 38" ≈ "туфли 38".
     */
    public function normalizeSearchQuery(?string $search): string
    {
        $tokens = preg_split(
            '/\s+/u',
            mb_strtolower(trim((string)$search)),
            -1,
            PREG_SPLIT_NO_EMPTY,
        ) ?: [];

        return implode(' ', array_diff($tokens, self::SEARCH_NOISE_WORDS));
    }

    /**
     * @param  array<string, array<array-key, Url>>  $filters
     * @return list<array<string, mixed>>
     */
    public function buildSort(ProductSort $sort, array $filters, ?string $search): array
    {
        // Text search: best match (ES _score), then stable id. Ignore UI sort.
        if ($search) {
            return [
                ['_score' => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ];
        }

        return match ($sort) {
            ProductSort::Newness => [
                ['newness_rating' => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ],
            ProductSort::PriceUp => [
                ['price' => ['order' => 'asc']],
                ['id' => ['order' => 'asc']],
            ],
            ProductSort::PriceDown => [
                ['price' => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ],
            ProductSort::Rating => [
                [ProductRatingColumn::fromFilters($filters)->value => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ],
        };
    }
}
