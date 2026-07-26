<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by statuses keyword.
 */
class StatusAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'statuses';

    private const SIZE = 50;

    private const AGGREGATION_NAME = 'statuses';

    /**
     * Build statuses aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Statuses aggregation
     */
    public function build(array $filters): array
    {
        return [
            'statuses' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
