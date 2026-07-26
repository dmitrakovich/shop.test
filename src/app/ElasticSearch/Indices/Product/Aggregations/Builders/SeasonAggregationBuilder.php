<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by seasons.id.
 */
class SeasonAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'seasons.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'seasons';

    /**
     * Build seasons aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Seasons aggregation
     */
    public function build(array $filters): array
    {
        return [
            'seasons' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
