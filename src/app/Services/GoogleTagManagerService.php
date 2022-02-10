<?php

namespace App\Services;

use App\Facades\Currency;
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
        $currentCurrencyCode = Currency::getCurrentCurrency()->code;
        Currency::setCurrentCurrency('USD', false);

        GoogleTagManagerFacade::view('cart', [
            'ids' => $cart->items->implode('product_id', ','),
            'value' => $cart->getTotalPrice(),
        ]);

        Currency::setCurrentCurrency($currentCurrencyCode, false);
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
     * Generate & return dataLayer script for view event for index page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return DataLayer
     */
    public function getViewForCatalog($products, $category, ?string $searchQuery = null): DataLayer
    {
        $this->setViewForCatalog($products, $category, $searchQuery);

        return GoogleTagManagerFacade::getDataLayer();
    }

    /**
     * Set GTM view event for index page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return void
     */
    public function setViewForCatalog($products, $category, ?string $searchQuery = null): void
    {
        if ($category instanceof Category) {
            $category = $category->getNameForGTM();
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
}
