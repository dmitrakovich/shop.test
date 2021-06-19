<?php

namespace App\Facades;

use App\Services\SaleService;
use Illuminate\Support\Facades\Facade;
/**
 * @method static \App\Models\Sale|null getCurrentSale() Получить текущую действующую акцию
 * @method static void applyForProduct(\App\Models\Product $product) Применить скидку к продукту
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
