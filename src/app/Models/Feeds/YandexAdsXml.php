<?php

namespace App\Models\Feeds;

use App\Models\Product;
use App\Services\Feeds\SizeRangeFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class YandexAdsXml extends YandexXml
{
    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'yandex_ads';
    }

    public function getViewName(): string
    {
        return 'yandex';
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
