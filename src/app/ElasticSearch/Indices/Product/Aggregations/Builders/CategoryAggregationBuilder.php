<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by categories.id.
 */
class CategoryAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'categories.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'categories';

    /**
     * Build categories aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Categories aggregation
     */
    public function build(array $filters): array
    {
        return [
            'categories' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
