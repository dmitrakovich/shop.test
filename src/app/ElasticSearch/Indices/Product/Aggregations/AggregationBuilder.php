<?php

namespace App\ElasticSearch\Indices\Product\Aggregations;

use App\ElasticSearch\Indices\Product\Contracts\AggregationBuilderInterface;

/**
 * Composite catalog aggregation builder.
 */
class AggregationBuilder
{
    /** @var list<AggregationBuilderInterface> */
    private readonly array $builders;

    /**
     * @param  AggregationBuilderInterface  ...$builders  Aggregation builders
     */
    public function __construct(AggregationBuilderInterface ...$builders)
    {
        $this->builders = $builders;
    }

    /**
     * Build all catalog aggregations.
     *
     * @param  array<string, mixed>  $filters  Active filters
     * @return array<string, mixed> Aggregations body
     */
    public function build(array $filters): array
    {
        $result = [];

        foreach ($this->builders as $builder) {
            $result = array_merge($result, $builder->build($filters));
        }

        return $result;
    }
}
