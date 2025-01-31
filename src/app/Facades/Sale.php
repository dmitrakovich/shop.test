<?php

namespace App\Facades;

use App\Services\SaleService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void applyForProduct(\App\Models\Product $product) Apply sale for Product model
 * @method static boolean hasFitting() Check if fitting is available
 * @method static boolean hasInstallment() Check if installment is available
 * @method static boolean hasCOD() Check if cash on delivery (COD) is available
 * @method static void applyToCart(\App\Models\Cart $cart) Apply sales to cart
 * @method static void applyToOrder(\App\Models\Cart $cart, \App\Models\Data\OrderData $orderData) Apply sales to order
 * @method static void disableUserSale() Disable user sale for some conditions
 * @method static void enableUserSale() Enable user sale after disable
 * @method static void applyPromocode(string $promocodeCode) Apply a promocode to the user's cart.
 *
 * @see \App\Services\SaleService
 */
class Sale extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SaleService::class;
    }
}
