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
use Elastic\Adapter\Search\Hit;
use Elastic\Adapter\Search\SearchParameters;
use Elastic\Adapter\Search\SearchResult;

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

    /**
     * Shorter tokens ("38") are sizes rather than SKU or id fragments.
     */
    private const int FRAGMENT_MIN_LENGTH = 3;

    /**
     * Constant score of a SKU substring hit: above text field matches,
     * below a whole SKU part matched on sku.text.
     */
    private const int SKU_FRAGMENT_BOOST = 50;

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

        $productIds = $this->productIds($result);

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
     * Products for the search dropdown, ranked like the catalog search
     * but matching the word being typed as a prefix.
     *
     * @return list<int>
     */
    public function suggestProductIds(string $search, int $limit): array
    {
        $parameters = $this->buildProductSuggestionParameters($search, $limit);

        return $parameters === null ? [] : $this->productIds($this->documents->search($parameters));
    }

    public function buildProductSuggestionParameters(string $search, int $limit): ?SearchParameters
    {
        $query = $this->buildSearchClause($search, prefixLastWord: true);
        if ($query === null) {
            return null;
        }

        return (new SearchParameters())
            ->indices([(string)config('catalog.elasticsearch.alias')])
            ->query($query)
            ->size($limit)
            ->source(false)
            ->sort([
                ['_score' => ['order' => 'desc']],
                [ProductRatingColumn::Rating->value => ['order' => 'desc']],
                ['id' => ['order' => 'desc']],
            ]);
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
            $aggregations['facet_' . $facet->value] = [
                'filter' => $this->filterClause($filterGroups, $model),
                'aggs' => [
                    'values' => [
                        'terms' => [
                            'field' => $facet->field(),
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
            'filter' => $this->filterClause($filterGroups, $excludedGroup),
            'aggs' => [
                'value' => [$metric => ['field' => 'price']],
            ],
        ];
    }

    /**
     * @param  array<class-string, list<array<string, mixed>>>  $filterGroups
     * @return array<string, mixed>
     */
    private function filterClause(array $filterGroups, ?string $excludedGroup = null): array
    {
        return $this->combineFilterGroups($filterGroups, $excludedGroup)
            ?? ['match_all' => (object)[]];
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
     * @param  bool  $prefixLastWord  Match the last word as a prefix (search-as-you-type).
     * @return array<string, mixed>|null
     */
    public function buildSearchClause(?string $search, bool $prefixLastWord = false): ?array
    {
        $search = $this->normalizeSearchQuery($search);
        if ($search === '') {
            return null;
        }

        $searchService = new SearchService($search);

        if ($searchService->useSimpleSearch()) {
            return $this->buildNumberClause($searchService->getIds()[0]);
        }

        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words === []) {
            return null;
        }

        $n = count($words);
        $should = [];
        foreach ($words as $index => $word) {
            $should[] = $this->buildWordClause($word, $prefixLastWord && $index === $n - 1);
        }

        return [
            'bool' => [
                'should' => $should,
                'minimum_should_match' => $n <= 2 ? $n : $n - 1,
            ],
        ];
    }

    /**
     * A single number is an id, a SKU part or a size.
     * From 3 digits it is also an id prefix: short_name ends with the id ("974" → "Ботинки 9744").
     * The exact id still ranks first through the short_name match.
     *
     * @return array<string, mixed>
     */
    private function buildNumberClause(int $number): array
    {
        $token = (string)$number;

        $should = [
            ['term' => ['id' => $number]],
            ['wildcard' => ['sku' => ['value' => '*' . $token . '*']]],
            ['match' => ['sizes.name' => ['query' => $token, 'operator' => 'and']]],
            ['match' => ['short_name' => ['query' => $token, 'operator' => 'and']]],
        ];

        if (strlen($token) >= self::FRAGMENT_MIN_LENGTH) {
            $should[] = ['prefix' => ['short_name' => ['value' => $token]]];
        }

        return [
            'bool' => [
                'should' => $should,
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * Stop words ("с", "на") analyze to nothing and match everything,
     * so they don't count against minimum_should_match.
     * Words with digits also match any part of the SKU ("HT86" → "HT860L-G01-S69").
     *
     * @return array<string, mixed>
     */
    private function buildWordClause(string $word, bool $isPrefix): array
    {
        $textClause = [
            'multi_match' => [
                'query' => $word,
                'fields' => self::SEARCH_FIELDS,
                'type' => $isPrefix ? 'bool_prefix' : 'best_fields',
                'fuzziness' => 'AUTO',
                'prefix_length' => 1,
                'max_expansions' => 50,
                'zero_terms_query' => 'all',
            ],
        ];

        if (mb_strlen($word) < self::FRAGMENT_MIN_LENGTH || preg_match('/\d/', $word) !== 1) {
            return $textClause;
        }

        return [
            'dis_max' => [
                'queries' => [
                    $textClause,
                    [
                        'wildcard' => [
                            'sku' => [
                                'value' => '*' . addcslashes($word, '*?\\') . '*',
                                'case_insensitive' => true,
                                'boost' => self::SKU_FRAGMENT_BOOST,
                            ],
                        ],
                    ],
                ],
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
     * @return list<int>
     */
    private function productIds(SearchResult $result): array
    {
        return $result->hits()
            ->map(static fn (Hit $hit): int => (int)$hit->document()->id())
            ->values()
            ->all();
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
                [ProductRatingColumn::newnessFromFilters($filters)->value => ['order' => 'desc']],
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
