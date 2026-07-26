<?php

namespace App\Sections\Catalog\Services;

use App\Data\Catalog\FilterStateData;
use App\Helpers\UrlHelper;
use App\Models\Url;

/**
 * Build catalog URLs from filter state.
 */
class FilterUrlBuilder
{
    /**
     * Build catalog URL from active Url filters.
     *
     * @param  FilterStateData  $filterState  Filter state
     * @param  array<string, mixed>|null  $dictionaries  Unused, kept for API compatibility
     * @param  bool  $includePageAndSort  Include page/sort query params
     * @return string Relative or front catalog URL
     */
    public function buildUrl(
        FilterStateData $filterState,
        ?array $dictionaries = null,
        bool $includePageAndSort = true
    ): string {
        UrlHelper::setCurrentFilters($filterState->urlFilters);

        $query = [];
        if ($includePageAndSort) {
            if ($filterState->sort) {
                $query['sort'] = $filterState->sort;
            }
            if ($filterState->page > 1) {
                $query['page'] = $filterState->page;
            }
        }
        if ($filterState->search) {
            $query['search'] = $filterState->search;
        }
        if ($filterState->priceMin !== null) {
            $query['price_min'] = $filterState->priceMin;
        }
        if ($filterState->priceMax !== null) {
            $query['price_max'] = $filterState->priceMax;
        }

        $url = UrlHelper::generate();
        if ($query !== []) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator.http_build_query($query);
        }

        return $url;
    }

    /**
     * Build URL path from active filter slugs (without front_route).
     *
     * @param  FilterStateData  $filterState  Filter state
     * @return string Catalog path
     */
    public function buildPath(FilterStateData $filterState): string
    {
        $basePath = (string)config('catalog.url.base_path', 'catalog');
        $slugs = [];

        foreach ($filterState->urlFilters as $group) {
            foreach ($group as $url) {
                if ($url instanceof Url && $url->slug) {
                    $slugs[] = (string)$url->slug;
                }
            }
        }

        if ($slugs === []) {
            return $basePath;
        }

        return $basePath.'/'.implode('/', $slugs);
    }

    /**
     * Get toggle URL and active flag for a facet item.
     *
     * @param  FilterStateData  $state  Current state
     * @param  string  $filterKey  ES filter key (e.g. brand.id)
     * @param  int|string  $id  Facet value id or status slug
     * @param  string  $slug  Facet slug
     * @return array{url: string, active: bool} Toggle route
     */
    public function getFilterItemRoute(
        FilterStateData $state,
        string $filterKey,
        int|string $id,
        string $slug = '',
    ): array {
        $activeValues = $state->activeFilters[$filterKey] ?? [];
        $active = in_array($id, $activeValues, false)
            || in_array((string)$id, array_map('strval', $activeValues), true);

        $path = $this->buildPath($state);
        if ($slug !== '') {
            $segments = array_values(array_filter(explode('/', str_replace(
                (string)config('catalog.url.base_path', 'catalog').'/',
                '',
                $path
            ))));

            if ($active) {
                $segments = array_values(array_filter($segments, fn ($s) => $s !== $slug));
            } else {
                $segments[] = $slug;
            }

            $base = (string)config('catalog.url.base_path', 'catalog');
            $path = $segments === [] ? $base : $base.'/'.implode('/', $segments);
        }

        return ['url' => $path, 'active' => $active];
    }

    /**
     * Get sort variant route.
     *
     * @param  FilterStateData  $state  Current state
     * @param  string  $sortValue  Sort key
     * @return array{url: string, active: bool} Sort route
     */
    public function getSortRoute(FilterStateData $state, string $sortValue): array
    {
        $path = $this->buildPath($state);
        $query = ['sort' => $sortValue];
        if ($state->search) {
            $query['search'] = $state->search;
        }

        return [
            'url' => $path.'?'.http_build_query($query),
            'active' => ($state->sort ?? 'newness') === $sortValue,
        ];
    }
}
