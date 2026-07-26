<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by sizes.id.
 */
class SizeAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'sizes.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'sizes';

    /**
     * Build sizes aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Sizes aggregation
     */
    public function build(array $filters): array
    {
        return [
            'sizes' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
