<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

/**
 * Aggregation by brand.id.
 */
class BrandAggregationBuilder extends AbstractAggregationBuilder
{
    private const FIELD = 'brand.id';

    private const SIZE = 500;

    private const AGGREGATION_NAME = 'brands';

    /**
     * Build brands aggregation.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Brands aggregation
     */
    public function build(array $filters): array
    {
        return [
            'brands' => $this->buildAggregation(
                self::FIELD,
                $filters,
                self::AGGREGATION_NAME,
                self::SIZE
            ),
        ];
    }
}
