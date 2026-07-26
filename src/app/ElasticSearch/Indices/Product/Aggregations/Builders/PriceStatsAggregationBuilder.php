<?php

namespace App\ElasticSearch\Indices\Product\Aggregations\Builders;

use App\ElasticSearch\Indices\Product\Filters\DefaultProductFilter;

/**
 * Stats aggregation on price for filter bounds.
 */
class PriceStatsAggregationBuilder extends AbstractAggregationBuilder
{
    private const AGG_NAME = 'price_stats';

    /**
     * Build price stats aggregation excluding the price range filter.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Price stats aggregation
     */
    public function build(array $filters): array
    {
        return [
            'price_stats' => [
                'filter' => [
                    'bool' => [
                        'must' => $this->getFiltersForAggregation(
                            DefaultProductFilter::PRICE_RANGE_KEY,
                            $filters
                        ),
                    ],
                ],
                'aggs' => [
                    self::AGG_NAME => [
                        'stats' => [
                            'field' => 'price',
                        ],
                    ],
                ],
            ],
        ];
    }
}
