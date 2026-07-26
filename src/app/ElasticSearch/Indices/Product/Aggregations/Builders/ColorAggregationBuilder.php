<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by colors.id.
 */
class ColorAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'colors.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'colors';

    /**
     * Build colors aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Colors aggregation
     */
    public function build(array $filters): array
    {
        return [
            'colors' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
