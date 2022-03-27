<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class Country extends Model
{
    use HasFactory;

    /**
     * Countries cache list
     *
     * @var array
     */
    public static $countries = null;

    /**
     * Get countries collection
     *
     * @return EloquentCollection
     */
    public static function getAll(): EloquentCollection
    {
        return self::$countries ?? (self::$countries = self::all());
    }

    /**
     * Return current country by user addr or ip
     *
     * @return self
     */
    public static function getCurrent(): self
    {
        $userCountryId = optional(auth()->user())->getFirstAddressCountryId();

        return self::getAll()->where('id', $userCountryId)->first()
            ?? self::getAll()->where('code', SxGeo::getCountry())->first();
    }
}
