<?php

namespace App\Services\Elasticsearch;

/**
 * @phpstan-type CatalogSearchHitId int
 */
final class CatalogSearchResult
{
    /**
     * @param  list<int>  $productIds  Ordered product IDs from Elasticsearch
     */
    public function __construct(
        public readonly array $productIds,
        public readonly int $total,
        public readonly float $minPrice,
        public readonly float $maxPrice,
    ) {}
}
