<?php

namespace App\Facades;

use App\Services\SaleService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Sale|null getCurrentSale() Получить текущую действующую акцию
 * @method static void applyForProduct(\App\Models\Product $product) Применить скидку к продукту
 * @method static boolean hasFitting() Проверить возможноть доставки с примеркой для текущей акции
 * @method static boolean hasInstallment() Проверить возможноть оплаты в рассрочку для текущей акции
 * @method static void applyForCart(\App\Models\Cart $cart) Применить скидку к корзине
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
