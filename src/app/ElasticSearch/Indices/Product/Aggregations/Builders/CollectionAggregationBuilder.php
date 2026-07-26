<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by collection.id.
 */
class CollectionAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'collection.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'collections';

    /**
     * Build collections aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Collections aggregation
     */
    public function build(array $filters): array
    {
        return [
            'collections' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
