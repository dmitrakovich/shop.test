<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

use App\ElasticSearch\Indices\Product\Contracts\AggregationBuilderInterface;
use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;

/**
 * Base builder for facet aggregations.
 */
abstract class AbstractAggregationBuilder implements AggregationBuilderInterface
{
    /**
     * Get filters for aggregation (excluding the target field).
     *
     * @param  string  $excludeField  Field to exclude
     * @param  array<string, mixed>  $filters  Active filters
     * @return list<array<string, mixed>> Bool must clauses
     */
    protected function getFiltersForAggregation(string $excludeField, array $filters): array
    {
        $result = [];
        $result[] = [
            'exists' => [
                'field' => 'images.path',
            ],
        ];

        $priceRange = $filters[DefaultProductFilter::PRICE_RANGE_KEY] ?? null;
        $activeFilters = $filters;
        unset($activeFilters[DefaultProductFilter::PRICE_RANGE_KEY]);

        if (
            is_array($priceRange)
            && $excludeField !== DefaultProductFilter::PRICE_RANGE_KEY
        ) {
            $range = [];
            if (isset($priceRange['min']) && $priceRange['min'] !== '' && $priceRange['min'] !== null) {
                $range['gte'] = (float)$priceRange['min'];
            }
            if (isset($priceRange['max']) && $priceRange['max'] !== '' && $priceRange['max'] !== null) {
                $range['lte'] = (float)$priceRange['max'];
            }
            if ($range !== []) {
                $result[] = [
                    'range' => [
                        'price' => $range,
                    ],
                ];
            }
        }

        foreach ($activeFilters as $name => $filter) {
            if ($name === $excludeField || !is_array($filter)) {
                continue;
            }

            $filterValues = [];
            foreach ($filter as $value) {
                $filterValues[] = $value;
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
     * Build one filter+terms aggregation.
     *
     * @param  string  $field  Terms field
     * @param  array<string, mixed>  $filters  Active filters
     * @param  string  $aggregationName  Nested aggregation name
     * @param  int  $size  Terms size
     * @return array<string, mixed> Aggregation body
     */
    protected function buildAggregation(
        string $field,
        array $filters,
        string $aggregationName,
        int $size
    ): array {
        return [
            'filter' => [
                'bool' => [
                    'must' => $this->getFiltersForAggregation($field, $filters),
                ],
            ],
            'aggs' => [
                $aggregationName => [
                    'terms' => [
                        'field' => $field,
                        'size' => $size,
                    ],
                ],
            ],
        ];
    }
}
