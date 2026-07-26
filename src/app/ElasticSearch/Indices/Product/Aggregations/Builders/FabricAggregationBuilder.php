<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by fabrics.id.
 */
class FabricAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'fabrics.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'fabrics';

    /**
     * Build fabrics aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Fabrics aggregation
     */
    public function build(array $filters): array
    {
        return [
            'fabrics' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
