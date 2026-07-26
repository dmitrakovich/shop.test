<?php

namespace App\Data\Catalog;

use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;

/**
 * Catalog filter state after parsing URL and query parameters.
 */
class FilterStateData
{
    /**
     * @param  array<string, mixed>  $activeFilters  Active filters for Elasticsearch
     * @param  list<array<string, mixed>>  $filterTags  Active filter tags for UI
     * @param  array<string, array<string, \App\Models\Url>>  $urlFilters  Original Url filters grouped by model
     * @param  string|null  $search  Search string
     * @param  string|null  $sort  Sort key
     * @param  int  $page  Page number (1-based)
     * @param  float|null  $priceMin  Lower price bound
     * @param  float|null  $priceMax  Upper price bound
     * @param  bool  $activeOptions  Whether non-category filters are active
     */
    public function __construct(
        public array $activeFilters = [],
        public array $filterTags = [],
        public array $urlFilters = [],
        public ?string $search = null,
        public ?string $sort = 'newness',
        public int $page = 1,
        public ?float $priceMin = null,
        public ?float $priceMax = null,
        public bool $activeOptions = false,
    ) {}

    /**
     * Create an empty filter state.
     *
     * @return self New state
     */
    public static function create(): self
    {
        return new self(
            activeFilters: [],
            filterTags: [],
            urlFilters: [],
            search: null,
            sort: 'newness',
            page: 1,
            priceMin: null,
            priceMax: null,
            activeOptions: false,
        );
    }

    /**
     * Check whether any filters, search, or price range are active.
     *
     * @return bool Whether selection constraints exist
     */
    public function hasActiveFilters(): bool
    {
        return $this->activeFilters !== []
            || $this->search !== null
            || $this->priceMin !== null
            || $this->priceMax !== null;
    }

    /**
     * Get filters for Elasticsearch (including _price_range when needed).
     *
     * @return array<string, mixed> Filters for CatalogQuery
     */
    public function getElasticFilters(): array
    {
        $filters = $this->activeFilters;

        if ($this->priceMin !== null || $this->priceMax !== null) {
            $filters[DefaultProductFilter::PRICE_RANGE_KEY] = [
                'min' => $this->priceMin,
                'max' => $this->priceMax,
            ];
        }

        return $filters;
    }
}
