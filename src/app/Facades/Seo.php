<?php

namespace App\Facades;

use App\Models\Product;
use App\Services\Seo\TitleGenerotorService;

class Seo
{
    /**
     * Return generated title for product page
     */
    public static function getProductTitle(Product $product): string
    {
        return app(TitleGenerotorService::class)->getProductTitle($product);
    }

    /**
     * Return generated description for product page
     */
    public static function getProductDescription(Product $product): string
    {
        return app(TitleGenerotorService::class)->getProductDescription($product);
    }

    /**
     * Return generated title for catalog page
     */
    public function getCatalogTitle(string $page, ...$params)
    {
        # code...
    }
}
