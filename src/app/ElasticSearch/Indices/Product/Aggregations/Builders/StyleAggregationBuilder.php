<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by styles.id.
 */
class StyleAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'styles.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'styles';

    /**
     * Build styles aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Styles aggregation
     */
    public function build(array $filters): array
    {
        return [
            'styles' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
