<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by heels.id.
 */
class HeelAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'heels.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'heels';

    /**
     * Build heels aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Heels aggregation
     */
    public function build(array $filters): array
    {
        return [
            'heels' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
