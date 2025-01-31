<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Cart getCart() Get cart model with data
 * @method static void addItem() Add item to cart
 * @method static void clear($onlyAvailable = false) Clear items from the shopping cart.
 * @method static void clearPromocode() Clear the applied promocode from the cart.
 *
 * @mixin \App\Models\Cart
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
