<?php

namespace App\Models\Feeds;

use App\Models\Product;
use App\Services\Feeds\SizeRangeFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Google Ads dynamic-remarketing feed without shoe models that have only 1-2 sizes left.
 */
class GoogleAdsCsv extends GoogleCsv
{
    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'google_ads';
    }

    /**
     * @return EloquentCollection<array-key, Product>
     */
    protected function getFeedProducts(): EloquentCollection
    {
        return parent::getFeedProducts()
            ->filter(fn (Product $product): bool => SizeRangeFilter::eligible($product))
            ->values();
    }
}
