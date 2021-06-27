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
     * Current currency code
     *
     * @var string
     */
    protected $currencyCode;

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
        Cookie::queue('current_currency', $this->currencyCode, $tenYears);
    }

    /**
     * Устновить текущую валюту
     *
     * @param string $currency
     * @return void
     */
    public function setCurrentCurrency(?string $currency = null)
    {
        if ($currency && isset($this->allCurrencies[$currency])) {
            $this->currencyCode = $currency;
            $this->saveCurrentCurrency();
        } elseif (Cookie::has('current_currency')) {
            $this->currencyCode = Cookie::get('current_currency');
        } else {
            $this->currencyCode = $this->getCurrencyByCountry();
            $this->saveCurrentCurrency();
        }
    }

    /**
     * Get swither
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
            'currentCurrency' => $this->currencyCode,
        ]);
    }

    /**
     * Получить валюту по стране
     *
     * @return string код валюты 3 буквы (ISO 4217)
     */
    protected function getCurrencyByCountry()
    {
        foreach ($this->allCurrencies as $currency) {
            if ($currency->country == $this->countryCode) {
                return $currency->code;
            }
        }
        return self::DEFAULT_CURRENCY;
    }
}
