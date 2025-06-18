<?php

namespace App\Http\Controllers;

use App\Data\Money\CurrencyData;
use App\Facades\Currency;
use Illuminate\Http\RedirectResponse;

class CurrencyController extends Controller
{
    /**
     * Переключить валюту
     */
    public function switch(CurrencyData $currencyData): RedirectResponse
    {
        Currency::setCurrentCurrency($currencyData->currency->value);

        return back();
    }
}
