<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Cart getCart() Get cart model with data
 * @method static void clear($onlyAvailable = false) Clear items from the shopping cart.
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
