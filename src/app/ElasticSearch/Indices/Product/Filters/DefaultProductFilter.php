<?php

namespace App\ElasticSearch\Indices\Product\Filters;

class DefaultProductFilter
{
    public const PRICE_RANGE_KEY = '_price_range';

    /**
     * Build post_filter clauses for the catalog.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @param  string|null  $deleteField  Field to exclude (facets)
     * @return list<array<string, mixed>> Bool filter clauses
     */
    public function getFilter(array $filters, ?string $deleteField = null): array
    {
        $result = [];
        $result[] = [
            'exists' => [
                'field' => 'images.path',
            ],
        ];

        $priceRange = $filters[self::PRICE_RANGE_KEY] ?? null;
        $activeFilters = $filters;
        unset($activeFilters[self::PRICE_RANGE_KEY]);

        if (
            is_array($priceRange)
            && ($deleteField === null || $deleteField !== self::PRICE_RANGE_KEY)
        ) {
            $clause = $this->priceRangeClause($priceRange);
            if ($clause !== null) {
                $result[] = $clause;
            }
        }

        foreach ($activeFilters as $name => $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $filterValues = [];
            foreach ($filter as $f) {
                if (isset($deleteField) && $deleteField != $name) {
                    $filterValues[] = $f;
                }
                if (!isset($deleteField)) {
                    $filterValues[] = $f;
                }
            }

            if ($filterValues !== []) {
                $result[] = [
                    'terms' => [
                        $name => $filterValues,
                    ],
                ];
            }
        }

        return $result;
    }

    /**
     * Build a range clause for price.
     *
     * @param  array{min?: mixed, max?: mixed}  $priceRange  Price bounds
     * @return array<string, mixed>|null Range clause or null
     */
    private function priceRangeClause(array $priceRange): ?array
    {
        $range = [];

        if (isset($priceRange['min']) && $priceRange['min'] !== '' && $priceRange['min'] !== null) {
            $range['gte'] = (float)$priceRange['min'];
        }

        if (isset($priceRange['max']) && $priceRange['max'] !== '' && $priceRange['max'] !== null) {
            $range['lte'] = (float)$priceRange['max'];
        }

        if ($range === []) {
            return null;
        }

        return [
            'range' => [
                'price' => $range,
            ],
        ];
    }
}
