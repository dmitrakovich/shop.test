<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class Country extends Model
{
    use HasFactory;

    final const DEFAULT_COUNTRY_CODE = 'BY';

    /**
     * Countries cache list
     */
    public static ?EloquentCollection $countries = null;

    /**
     * Get countries collection
     *
     * @return EloquentCollection<self>
     */
    public static function getAll()
    {
        return self::$countries ?? (self::$countries = self::all());
    }

    /**
     * Return default country
     *
     * @return self
     */
    public static function getDefaultCountry()
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
