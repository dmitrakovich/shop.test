<?php

namespace App\Services;

use App\Facades\Sale;
use App\Models\Cart;
use App\Models\CartData;

class CartService
{
    public function __construct(private Cart $cart) {}

    /**
     * Initialize and load the user's shopping cart.
     */
    public function initCart(): Cart
    {
        $cart = client()->cart ?? new Cart([]);
        $cart->load('items');

        return $cart;
    }

    /**
     * Prepare the user's shopping cart for display.
     */
    public function prepareCart(Cart $cart): Cart
    {
        $cart->items->loadMissing([
            'product.brand',
            'product.category',
            'product.media',
            'product.styles',
            'product.sizes',
            'size:id,name',
        ]);

        /** @var CartData $item */
        foreach ($cart->items as $key => $item) {
            if (empty($item->product)) {
                $item->delete();
                $cart->items->forget($key);
            }
            $product = $item->product;
            if ($product->trashed() || !$product->sizes->contains('id', $item->size_id)) {
                $item->setNotAvailable();
            }
        }

        return $cart;
    }

    /**
     * Calc & return cart prices
     */
    public function getCartPrices(Cart $cart): array
    {
        Sale::disableUserSale();
        Sale::applyToCart($cart);
        $totalPriceWithoutUserSale = $cart->getTotalPrice();

        Sale::enableUserSale();
        Sale::applyToCart($cart);
        $totalPrice = $cart->getTotalPrice();
        $totalOldPrice = $cart->getTotalOldPrice();

        return compact('totalPrice', 'totalOldPrice', 'totalPriceWithoutUserSale');
    }
}
