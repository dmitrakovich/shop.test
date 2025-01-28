<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
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
        $this->setCurrentCountry();
        $this->setAllCurrencies();
        $this->setCurrentCurrency();
    }

    /**
     * Set current country
     *
     * @param  string  $countryCode
     * @return void
     */
    protected function setCurrentCountry($countryCode = null)
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
                ->keyBy('code')
                ->toArray();
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
     * Set & save current currency
     *
     * @param  bool  $save
     */
    public function setCurrentCurrency(?string $currencyCode = null, $save = true): void
    {
        if ($currencyCode) {
            $this->setCurrencyByCode($currencyCode);
        } elseif (Cookie::has('current_currency')) {
            $this->setCurrencyByCode(Cookie::get('current_currency'));
            $save = false;
        } else {
            $this->setCurrencyByCode($this->getCurrencyCodeByCountry());
        }

        if ($save) {
            $this->saveCurrentCurrency();
        }
    }

    /**
     * Set currency by code
     *
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
     * Convert price in needed or current currency
     */
    public function convert(float $priceInByn, ?string $currencyCode = null): float
    {
        $currency = $this->allCurrencies[$currencyCode] ?? $this->currency;
        $precision = 10 ** $currency->decimals;
        $priceInCurrency = $priceInByn * $currency->rate;

        return ceil(round($priceInCurrency * $precision, $currency->decimals)) / $precision;
    }

    /**
     * Convert price from needed or current currency to byn
     */
    public function reverseConvert(float $priceInCurrency, ?string $currencyCode = null): float
    {
        $currency = $this->allCurrencies[$currencyCode] ?? $this->currency;
        $priceInByn = $priceInCurrency / $currency->rate;

        return ceil($priceInByn);
    }

    /**
     * Format price in current currency
     */
    public function format(float $price, ?string $currency = null, string $space = '&nbsp;'): string
    {
        $currency = $this->allCurrencies[$currency] ?? $this->currency;

        return number_format($price, $currency->decimals, '.', $space) . $space . $currency->symbol;
    }

    /**
     * Convert & format price in current currency
     */
    public function convertAndFormat(float $priceInByn): string
    {
        return $this->format($this->convert($priceInByn));
    }
}
