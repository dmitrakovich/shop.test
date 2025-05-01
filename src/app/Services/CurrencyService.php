<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class CurrencyService
{
    const DEFAULT_CURRENCY = 'BYN';

    /**
     * Current currency
     */
    private Currency $currency;

    /**
     * Current country code
     */
    private string $countryCode;

    /**
     * All currency list
     *
     * @var array<string, \stdClass>
     */
    private array $allCurrencies = [];

    public function __construct()
    {
        $this->setCurrentCountry();
        $this->setAllCurrencies();
        $this->setCurrentCurrency();
    }

    /**
     * Set current country
     */
    private function setCurrentCountry(?string $countryCode = null): void
    {
        $this->countryCode = $countryCode ?? SxGeo::getCountry();
    }

    /**
     * Установить все валюты
     */
    private function setAllCurrencies(): void
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
     * @todo move from cookie to device
     */
    private function saveCurrentCurrency(): void
    {
        $tenYears = 10 * 365 * 24 * 60 * 60;
        Cookie::queue('current_currency', $this->currency->code, $tenYears);
    }

    /**
     * Set & save current currency
     */
    public function setCurrentCurrency(?string $currencyCode = null, bool $save = true): void
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
     * Get Currency object by currency code
     */
    public function getCurrencyByCode(?string $currencyCode = null): Currency
    {
        if (is_null($currencyCode)) {
            return $this->getCurrentCurrency();
        }
        if (!isset($this->allCurrencies[$currencyCode])) {
            $currencyCode = $this->getCurrencyCodeByCountry();
        }

        return (new Currency())->forceFill((array)$this->allCurrencies[$currencyCode]);
    }

    /**
     * Set currency by code
     */
    private function setCurrencyByCode(string $currencyCode): void
    {
        $this->currency = $this->getCurrencyByCode($currencyCode);
    }

    /**
     * Get switcher view
     */
    public function getSwitcher(): ?View
    {
        if ($this->countryCode === 'BY') {
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
    private function getCurrencyCodeByCountry(): string
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
     */
    public function getCurrentCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Convert price in needed or current currency
     */
    public function convert(float $priceInByn, ?string $currencyCode = null): float
    {
        $currency = $this->getCurrencyByCode($currencyCode);
        $precision = 10 ** $currency->decimals;
        $priceInCurrency = $priceInByn * $currency->rate;

        return ceil(round($priceInCurrency * $precision, $currency->decimals)) / $precision;
    }

    /**
     * Convert price from needed or current currency to byn
     */
    public function reverseConvert(float $priceInCurrency, ?string $currencyCode = null): float
    {
        $currency = $this->getCurrencyByCode($currencyCode);
        $priceInByn = $priceInCurrency / $currency->rate;

        return ceil($priceInByn);
    }

    /**
     * Format price in current currency
     */
    public function format(float $price, ?string $currencyCode = null, string $space = '&nbsp;'): string
    {
        $currency = $this->getCurrencyByCode($currencyCode);

        return number_format($price, $currency->decimals, '.', $space) . $space . $currency->symbol;
    }

    /**
     * Convert & format price in current currency
     */
    public function convertAndFormat(float $priceInByn): string
    {
        return $this->format($this->convert($priceInByn));
    }

    /**
     * Round price
     */
    public function round(float $price): float
    {
        return round($price, $this->currency->decimals);
    }
}
