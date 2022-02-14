<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\GoogleTagManager\DataLayer;
use App\Models\{Cart, Category, Product};
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class GoogleTagManagerService
{
    /**
     * Set GTM view event for product page
     *
     * @param Product $product
     * @return void
     */
    public function setViewForProduct(Product $product): void
    {
        GoogleTagManagerFacade::view('product', ['id' => $product->id]);
    }

    /**
     * Set GTM view event for cart page
     *
     * @param Cart $cart
     * @return void
     */
    public function setViewForCart(Cart $cart): void
    {
        GoogleTagManagerFacade::view('cart', [
            'ids' => $cart->items->implode('product_id', ','),
            'value' => $cart->getTotalPrice('USD'),
        ]);
    }

    /**
     * Set GTM view event for order complete page
     *
     * @return void
     */
    public function setViewForOrder(): void
    {
        GoogleTagManagerFacade::view('order_complete');
    }

    /**
     * Set GTM view event for index page
     *
     * @return void
     */
    public function setViewForIndex(): void
    {
        GoogleTagManagerFacade::view('index');
    }

    /**
     * Set GTM view event for other pages
     *
     * @return void
     */
    public function setViewForOther(): void
    {
        GoogleTagManagerFacade::view('other');
    }

    /**
     * Set GTM view event for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return void
     */
    public function setViewForCatalog($products, $category, ?string $searchQuery = null): void
    {
        if ($category instanceof Category) {
            $category = $category->getNameWithParents();
        }

        if (!empty($searchQuery)) {
            GoogleTagManagerFacade::view('search_result', [
                'query' => $searchQuery,
                'ids' => $products->implode('id', ',')
            ]);
        } else {
            GoogleTagManagerFacade::view('catalog', [
                'category' => $category,
                'ids' => $products->implode('id', ',')
            ]);
        }
    }

    /**
     * Prepare products array
     *
     * @param Product $product
     * @return DataLayer
     */
    public static function prepareProduct(Product $product): DataLayer
    {
        return new DataLayer([
            'name' => $product->brand->name . ' '. $product->id,
            'id' => $product->id,
            'price' => $product->getPrice('USD'),
            'brand' => $product->brand->name,
            'category' => $product->category->getNameWithParents(),
        ]);
    }

    /**
     * Prepare products array
     *
     * @param Collection $products
     * @param integer|null $quantity
     * @return array
     */
    public function prepareProductsArray($products, ?int $quantity = null): array
    {
        return $products->map(function (Product $product) {
            return self::prepareProduct($product)->toArray();
        })->toArray();
    }

    /**
     * Set GTM ecommerce event for catalog page
     *
     * @param Collection $products
     * @return void
     */
    public function setEcommerceForCatalog($products): void
    {
        self::setEcommerceImpressions($this->prepareProductsArray($products));
    }

    /**
     * Set events for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return void
     */
    public function setForCatalog($products, $category, ?string $searchQuery = null): void
    {
        $this->setViewForCatalog($products, $category, $searchQuery);
        $this->setEcommerceForCatalog($products);
    }

    /**
     * Generate & return dataLayer script for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return array
     */
    public function getForCatalogArrays($products, $category, ?string $searchQuery = null): array
    {
        $this->setForCatalog($products, $category, $searchQuery);

        return GoogleTagManagerFacade::getPushData()->map(function (DataLayer $dataLayer) {
            return $dataLayer->toArray();
        })->toArray();
    }

    /**
     * Set GTM ecommerce product impressions event
     *
     * @param array $impressions
     * @return void
     */
    public static function setEcommerceImpressions(array $impressions): void
    {
        GoogleTagManagerFacade::ecommerce('productImpressions', [
            'currencyCode' => 'USD',
            'impressions' => $impressions,
        ]);
    }
}
