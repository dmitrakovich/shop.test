<?php

namespace App\Http\Controllers;

use App\Facades\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Переключить валюту
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request)
    {
        Currency::setCurrentCurrency((string)$request->input('currency'));
        return back();
    }
}
