<?php

namespace App\Facades;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Facade;
/**
 * @method static \Illuminate\Contracts\View\View|null getSwitcher() Получить view переключателя
 * @method static void setCurrentCurrency(?string $currency) установить текущую валюту
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
