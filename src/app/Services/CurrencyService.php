<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class CurrencyService
{
    const DEFAULT_CURRENCY = 'USD';

    /**
     * Current currency
     *
     * @var \App\Models\Currency
     */
    protected $currency;

    /**
     * Current country code
     *
     * @var string
     */
    protected $countryCode;

    /**
     * All currency list
     *
     * @var array
     */
    protected $allCurrencies = [];

    public function __construct()
    {
        $this->setCurrenctCountry();
        $this->setAllCurrencies();
        $this->setCurrentCurrency();
    }

    /**
     * Set current country
     *
     * @param string $countryCode
     * @return void
     */
    protected function setCurrenctCountry($countryCode = null)
    {
        $this->countryCode = $countryCode ?? SxGeo::getCountry();
    }

    /**
     * Установить все валюты
     *
     * @return void
     */
    protected function setAllCurrencies()
    {
        $this->allCurrencies = Cache::rememberForever('currencies', function () {
            return DB::table('currencies')
                ->get(['code', 'country', 'rate', 'decimals', 'symbol'])
                ->keyBy('code');
        });
    }

    /**
     * Save current currency in storage
     *
     * @return void
     */
    protected function saveCurrentCurrency()
    {
        $tenYears = 10 * 365 * 24 * 60 * 60;
        Cookie::queue('current_currency', $this->currency->code, $tenYears);
    }

    /**
     * Set current currency
     *
     * @param string $currency
     * @return void
     */
    public function setCurrentCurrency(?string $currencyCode = null)
    {
        if ($currencyCode) {
            $this->setCurrencyByCode($currencyCode);
            $this->saveCurrentCurrency();
        } elseif (Cookie::has('current_currency')) {
            $this->setCurrencyByCode(Cookie::get('current_currency'));
        } else {
            $this->setCurrencyByCode($this->getCurrencyCodeByCountry());
            $this->saveCurrentCurrency();
        }
    }

    /**
     * Set currency by code
     *
     * @param string $currencyCode
     * @return void
     */
    protected function setCurrencyByCode(string $currencyCode)
    {
        if (!isset($this->allCurrencies[$currencyCode])) {
            $currencyCode = $this->getCurrencyCodeByCountry();
        }
        $this->currency = $this->allCurrencies[$currencyCode] ?? $this->getDefaultCurrency();
    }

    /**
     * Get default currency
     *
     * @return \App\Models\Currency
     */
    protected function getDefaultCurrency()
    {
        return $this->allCurrencies[self::DEFAULT_CURRENCY];
    }

    /**
     * Get swither view
     *
     * @return \Illuminate\Contracts\View\View|null
     */
    public function getSwitcher()
    {
        if ($this->countryCode == 'BY') {
            return null;
        }
        return view('includes.currency-switcher', [
            'currenciesList' => $this->allCurrencies,
            'currentCurrency' => $this->currency->code,
        ]);
    }

    /**
     * Get currency code by country
     *
     * @return string currency code 3 symbol (ISO 4217)
     */
    protected function getCurrencyCodeByCountry()
    {
        foreach ($this->allCurrencies as $currency) {
            if ($currency->country == $this->countryCode) {
                return $currency->code;
            }
        }
        return self::DEFAULT_CURRENCY;
    }

    /**
     * Get current currency object
     *
     * @return \App\Models\Currency
     */
    public function getCurrentCurrency()
    {
        return $this->currency;
    }

    /**
     * Convert price in current currency
     *
     * @param float $priceInByn
     * @return float
     */
    public function convert(float $priceInByn): float
    {
        $precision = 10 ** $this->currency->decimals;
        $priceInCurrency = $priceInByn * $this->currency->rate;
        return ceil($priceInCurrency * $precision) / $precision;
    }

    /**
     * Format price in current currency
     *
     * @param float $price
     * @param string|null $currency
     * @return string
     */
    public function format(float $price, ?string $currency = null): string
    {
        $currency = $this->allCurrencies[$currency] ?? $this->currency;
        return number_format($price, $currency->decimals, '.', '&nbsp') . '&nbsp' . $currency->symbol;
    }

    /**
     * Conver & format price in current currency
     *
     * @param float $value
     * @return string
     */
    public function convertAndFormat(float $priceInByn): string
    {
        return $this->format($this->convert($priceInByn));
    }
}
