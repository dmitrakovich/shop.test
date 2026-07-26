<?php

namespace App\Services\Elasticsearch;

use App\Enums\Product\ProductRatingColumn;
use App\Enums\Product\ProductSort;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection as ProductCollection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Price;
use App\Models\ProductAttributes\Status;
use App\Models\ProductAttributes\Top;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;
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
     * Filter model class → Elasticsearch field (terms).
     *
     * @var array<class-string, string>
     */
    private const ATTRIBUTE_FIELDS = [
        Brand::class => 'brand_id',
        ProductCollection::class => 'collection_id',
        Season::class => 'season_id',
        Size::class => 'size_ids',
        Color::class => 'color_ids',
        Fabric::class => 'fabric_ids',
        Heel::class => 'heel_ids',
        Style::class => 'style_ids',
        Tag::class => 'tag_ids',
    ];

    public function __construct(
        private readonly DocumentManager $documents,
    ) {}

    /**
     * @param  array<string, array<string, Url>>  $filters
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

        $minPrice = (float)($result->aggregations()->get('min_price')?->raw()['value'] ?? 0);
        $maxPrice = (float)($result->aggregations()->get('max_price')?->raw()['value'] ?? 999);

        return new CatalogSearchResult(
            productIds: $productIds,
            total: (int)($result->total() ?? 0),
            minPrice: $minPrice,
            maxPrice: $maxPrice > 0 ? $maxPrice : 999,
        );
    }

    /**
     * Build ES request parameters (unit-testable without a live cluster).
     *
     * @param  array<string, array<string, Url>>  $filters
     */
    public function buildSearchParameters(
        array $filters,
        ProductSort $sort,
        ?string $search,
        int $page,
        int $perPage,
    ): SearchParameters {
        [$filterClauses, $pricePostFilter] = $this->buildFilterClauses($filters);
        $normalizedSearch = $this->normalizeSearchQuery($search);
        $searchClause = $this->buildSearchClause($search);

        $bool = [];
        if ($filterClauses !== []) {
            $bool['filter'] = $filterClauses;
        }
        if ($searchClause !== null) {
            $bool['must'] = [$searchClause];
        }

        $query = $bool === []
            ? ['match_all' => (object)[]]
            : ['bool' => $bool];

        $parameters = (new SearchParameters())
            ->indices([(string)config('catalog.elasticsearch.alias')])
            ->query($query)
            ->from(max(0, ($page - 1) * $perPage))
            ->size($perPage)
            ->trackTotalHits(true)
            ->source(false)
            ->sort($this->buildSort($sort, $filters, $normalizedSearch))
            ->aggregations([
                'min_price' => ['min' => ['field' => 'price']],
                'max_price' => ['max' => ['field' => 'price']],
            ]);

        if ($pricePostFilter !== null) {
            $parameters->postFilter($pricePostFilter);
        }

        return $parameters;
    }

    /**
     * Whether this request can be served from Elasticsearch (promotion is MySQL-only for now).
     *
     * @param  array<string, array<string, Url>>  $filters
     */
    public function supportsFilters(array $filters): bool
    {
        return !isset($filters[Status::class]['promotion']);
    }

    /**
     * @param  array<string, array<string, Url>>  $filters
     * @return array{0: list<array<string, mixed>>, 1: array<string, mixed>|null}
     */
    public function buildFilterClauses(array $filters): array
    {
        $clauses = [];
        $priceRange = [];

        foreach ($filters as $filterClass => $values) {
            if ($filterClass === Top::class) {
                $ids = array_map(intval(...), array_column($values, 'model_id'));
                if ($ids !== []) {
                    $clauses[] = [
                        'bool' => [
                            'must_not' => [
                                ['ids' => ['values' => array_map(strval(...), $ids)]],
                            ],
                        ],
                    ];
                }

                continue;
            }

            if ($filterClass === Price::class) {
                foreach ($values as $slug => $url) {
                    $price = $url->filters;
                    if (!$price instanceof Price) {
                        continue;
                    }
                    if (str_starts_with((string)$slug, 'price-from-')) {
                        $priceRange['gt'] = (float)$price->price;
                    } else {
                        $priceRange['lt'] = (float)$price->price;
                    }
                }

                continue;
            }

            if ($filterClass === Status::class) {
                foreach (array_keys($values) as $slug) {
                    if ($slug === 'promotion') {
                        continue;
                    }
                    $clauses[] = ['term' => ['status_slugs' => $slug]];
                }

                continue;
            }

            if ($filterClass === Category::class) {
                $last = end($values);
                $categoryId = (int)($last['model_id'] ?? 0);
                if ($categoryId !== 0 && $categoryId !== Category::ROOT_CATEGORY_ID) {
                    $clauses[] = ['term' => ['category_ids' => $categoryId]];
                }

                continue;
            }

            $field = self::ATTRIBUTE_FIELDS[$filterClass] ?? null;
            if ($field === null) {
                continue;
            }

            $ids = array_map(intval(...), array_column($values, 'model_id'));
            if ($ids === []) {
                continue;
            }

            $clauses[] = count($ids) === 1
                ? ['term' => [$field => $ids[0]]]
                : ['terms' => [$field => $ids]];
        }

        $pricePostFilter = $priceRange === []
            ? null
            : ['range' => ['price' => $priceRange]];

        return [$clauses, $pricePostFilter];
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
                        // Size names (e.g. "38") live in search_text after indexing.
                        ['match' => ['search_text' => ['query' => (string)$token, 'operator' => 'and']]],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];
        }

        return [
            'multi_match' => [
                'query' => $search,
                'fields' => ['search_text', 'sku^3', 'color_txt'],
                'type' => 'best_fields',
                'operator' => 'and',
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
     * @param  array<string, array<string, Url>>  $filters
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
