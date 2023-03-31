<?php

namespace App\Facades;

use App\Services\SaleService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void applyForProduct(\App\Models\Product $product) Применить скидку к продукту
 * @method static boolean hasFitting() Проверить возможноть доставки с примеркой для текущей акции
 * @method static boolean hasInstallment() Проверить возможноть оплаты в рассрочку для текущей акции
 * @method static void applyToCart(\App\Models\Cart $cart) Apply sales to cart
 * @method static void applyToOrder(\App\Models\Cart $cart, array $orderData) Apply sales to order
 * @method static void disableUserSale() Disable user sale for some conditions
 * @method static void enableUserSale() Enable user sale after disable
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
