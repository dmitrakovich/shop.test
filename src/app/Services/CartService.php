<?php

namespace App\Services;

use App\Facades\Sale;
use App\Models\Cart;
use App\Models\CartData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CartService
{
    public function __construct(private Cart $cart) {}

    /**
     * Initialize and load the user's shopping cart.
     */
    public function initCart(): Cart
    {
        $cart = $this->cart->query()->findOrNew($this->getCartId());
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
     * Get the cart ID for the current user or guest.
     */
    private function getCartId(): ?int
    {
        return Auth::user() ? Auth::user()->cart_token : Cookie::get('cart_token');
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
