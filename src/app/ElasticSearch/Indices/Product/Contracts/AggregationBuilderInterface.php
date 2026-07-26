<?php

namespace App\ElasticSearch\Indices\Product\Contracts;

/**
 * Interface for catalog aggregation builders.
 */
interface AggregationBuilderInterface
{
    /**
     * Build aggregations.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Aggregation name → body map
     */
    public function build(array $filters): array;
}
