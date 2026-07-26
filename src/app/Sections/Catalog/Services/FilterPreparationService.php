<?php

namespace App\Sections\Catalog\Services;

use App\Data\Catalog\FilterStateData;

/**
 * Prepare filter blocks from Elasticsearch aggregations.
 */
class FilterPreparationService
{
    /**
     * @param  FilterUrlBuilder  $filterUrlBuilder  Filter URL builder
     */
    public function __construct(
        private readonly FilterUrlBuilder $filterUrlBuilder,
    ) {}

    /**
     * Prepare filters and sort_list from ES buckets.
     *
     * @param  array<string, mixed>  $buckets  ES aggregations
     * @param  array<string, array<int|string, object>>  $dictionaries  Dictionaries by facet type
     * @param  FilterStateData  $state  Filter state
     * @return object{filters: object, sort_list: list<array{route: string, active: bool, title: string}>} Prepared data
     */
    public function prepareFilters(array $buckets, array $dictionaries, FilterStateData $state): object
    {
        $result = [];

        $facetMap = [
            'categories' => ['key' => 'categories.id', 'dict' => 'categories'],
            'brands' => ['key' => 'brand.id', 'dict' => 'brands'],
            'sizes' => ['key' => 'sizes.id', 'dict' => 'sizes'],
            'colors' => ['key' => 'colors.id', 'dict' => 'colors'],
            'fabrics' => ['key' => 'fabrics.id', 'dict' => 'fabrics'],
            'heels' => ['key' => 'heels.id', 'dict' => 'heels'],
            'seasons' => ['key' => 'seasons.id', 'dict' => 'seasons'],
            'styles' => ['key' => 'styles.id', 'dict' => 'styles'],
            'tags' => ['key' => 'tags.id', 'dict' => 'tags'],
            'collections' => ['key' => 'collection.id', 'dict' => 'collections'],
            'statuses' => ['key' => 'statuses', 'dict' => 'statuses', 'by_slug' => true],
        ];

        foreach ($buckets as $aggKey => $aggData) {
            if (!is_array($aggData)) {
                continue;
            }

            if ($aggKey === 'price_stats') {
                $result['price'] = $this->preparePriceStats($aggData, $state);

                continue;
            }

            if (!isset($facetMap[$aggKey])) {
                continue;
            }

            $config = $facetMap[$aggKey];
            $nestedBuckets = $aggData[$aggKey]['buckets'] ?? [];
            $dict = $dictionaries[$config['dict']] ?? [];

            $result[$aggKey] = $this->prepareFacetItems(
                $nestedBuckets,
                $dict,
                $state,
                $config['key'],
                (bool)($config['by_slug'] ?? false),
            );
        }

        return (object)[
            'filters' => (object)$result,
            'sort_list' => $this->buildSortList($state),
        ];
    }

    /**
     * Prepare facet items from terms buckets.
     *
     * @param  list<array{key?: mixed, doc_count?: int}>  $buckets  Terms buckets
     * @param  array<int|string, object>  $dictionary  Dictionary by id or slug
     * @param  FilterStateData  $state  Filter state
     * @param  string  $filterKey  ES filter key
     * @param  bool  $bySlug  Whether bucket key is a slug string
     * @return list<object> Facet items
     */
    private function prepareFacetItems(
        array $buckets,
        array $dictionary,
        FilterStateData $state,
        string $filterKey,
        bool $bySlug = false,
    ): array {
        $out = [];

        foreach ($buckets as $item) {
            $key = $item['key'] ?? null;
            if ($key === null) {
                continue;
            }

            $entity = $bySlug
                ? ($dictionary[(string)$key] ?? null)
                : ($dictionary[(int)$key] ?? null);

            if ($entity === null) {
                continue;
            }

            $id = $bySlug ? (string)$key : (int)$key;
            $slug = (string)($entity->slug ?? $key);
            $route = $this->filterUrlBuilder->getFilterItemRoute($state, $filterKey, $id, $slug);

            $out[] = (object)[
                'id' => $entity->id ?? $id,
                'name' => $entity->name ?? $entity->title ?? '',
                'slug' => $slug,
                'count' => (int)($item['doc_count'] ?? 0),
                'route' => $route['url'],
                'active' => $route['active'],
            ];
        }

        usort($out, fn ($a, $b) => strcmp((string)$a->name, (string)$b->name));

        return $out;
    }

    /**
     * Prepare price bounds from stats aggregation.
     *
     * @param  array<string, mixed>  $aggData  price_stats filter aggregation
     * @param  FilterStateData  $state  Filter state
     * @return object{bounds_min: float|null, bounds_max: float|null, active_min: float|null, active_max: float|null}
     */
    private function preparePriceStats(array $aggData, FilterStateData $state): object
    {
        $stats = $aggData['price_stats'] ?? [];
        $min = $stats['min'] ?? null;
        $max = $stats['max'] ?? null;

        return (object)[
            'bounds_min' => $min !== null ? round((float)$min, 2) : null,
            'bounds_max' => $max !== null ? round((float)$max, 2) : null,
            'active_min' => $state->priceMin,
            'active_max' => $state->priceMax,
        ];
    }

    /**
     * Build sort options list.
     *
     * @param  FilterStateData  $state  Filter state
     * @return list<array{route: string, active: bool, title: string}> Sort list
     */
    private function buildSortList(FilterStateData $state): array
    {
        $sortOptions = config('catalog.url.sort_options', []);
        $list = [];

        foreach ($sortOptions as $key => $title) {
            $route = $this->filterUrlBuilder->getSortRoute($state, (string)$key);
            $list[] = [
                'route' => $route['url'],
                'active' => $route['active'],
                'title' => (string)$title,
            ];
        }

        return $list;
    }
}
