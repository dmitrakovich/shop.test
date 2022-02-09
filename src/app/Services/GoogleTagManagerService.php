<?php

namespace App\Services;

use App\Facades\Currency;
use App\Models\Cart;
use App\Models\Product;
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
}
