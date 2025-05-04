<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $mask
 * @property string $img
 * @property string $prefix
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Country extends Model
{
    final const DEFAULT_COUNTRY_CODE = 'BY';

    /**
     * Countries cache list
     */
    public static ?Collection $countries = null;

    /**
     * Get countries collection
     *
     * @return Collection<self>
     */
    public static function getAll(): Collection
    {
        return self::$countries ?? (self::$countries = self::all());
    }

    /**
     * Return default country
     */
    public static function getDefaultCountry(): self
    {
        return self::getAll()->where('code', self::DEFAULT_COUNTRY_CODE)->first();
    }

    /**
     * Return current country by user addr or ip
     */
    public static function getCurrent(): self
    {
        $userCountryId = optional(auth()->user())->getFirstAddressCountryId();

        return self::getAll()->where('id', $userCountryId)->first()
            ?? self::getAll()->where('code', SxGeo::getCountry())->first()
            ?? self::getDefaultCountry();
    }
}
