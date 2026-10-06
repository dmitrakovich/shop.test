<?php

namespace App\Models\Feeds;

use App\Models\Product;
use App\Services\Feeds\SizeRangeFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GoogleAdsXml extends GoogleXml
{
    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'google_ads';
    }

    public function getViewName(): string
    {
        return 'google';
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
