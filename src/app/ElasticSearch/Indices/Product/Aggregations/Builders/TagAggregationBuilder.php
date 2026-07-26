<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by tags.id.
 */
class TagAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'tags.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'tags';

    /**
     * Build tags aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Tags aggregation
     */
    public function build(array $filters): array
    {
        return [
            'tags' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
