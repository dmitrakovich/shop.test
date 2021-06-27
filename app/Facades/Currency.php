<?php

namespace App\Facades;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Facade;
/**
 * @method static \Illuminate\Contracts\View\View|null getSwitcher() Get swither view
 * @method static void setCurrentCurrency(?string $currency) Set current currency
 * @method static \App\Models\Currency getCurrentCurrency() Get current currency object
 * @method static float convert(float $priceInByn) Convert price in current currency
 * @method static string format(float $price, ?string $currency = null) Format price in current currency
 * @method static string convertAndFormat(float $priceInByn) Conver & format price in current currency
 *
 * @see \App\Services\CurrencyService
 */
class Currency extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CurrencyService::class;
    }
}
