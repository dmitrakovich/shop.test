<?php

namespace App\Models\Feeds;

use App\Models\Collection;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class YandexBusinessXml extends YandexXml
{
    /**
     * Return part of a filename
     */
    public function getKey(): string
    {
        return 'yandex_business';
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
        $collectionId = Collection::getCurrentId();

        if ($collectionId === null) {
            return new EloquentCollection();
        }

        return (new ProductService())->getForFeed(collectionId: $collectionId);
    }
}
