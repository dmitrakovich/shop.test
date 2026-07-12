<?php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $mask
 * @property string $img
 * @property string $prefix
 */
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    final const string DEFAULT_COUNTRY_CODE = 'BY';

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * The table does not have created_at / updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Countries cache list
     *
     * @var Collection<int, self>|null
     */
    public static ?Collection $countries = null;

    /**
     * Get countries collection
     *
     * @return Collection<int, self>
     */
    public static function getAll(): Collection
    {
        if (self::$countries instanceof Collection) {
            return self::$countries;
        }

        /** @var Collection<int, self> $countries */
        $countries = self::all();

        return self::$countries = $countries;
    }

    /**
     * Return default country
     */
    public static function getDefaultCountry(): self
    {
        $country = self::getAll()->firstWhere('code', self::DEFAULT_COUNTRY_CODE);

        if (!$country instanceof self) {
            throw new RuntimeException('Default country is missing.');
        }

        return $country;
    }

    /**
     * Return current country by user addr or ip
     */
    public static function getCurrent(): self
    {
        $userCountryId = optional(auth()->user())->getFirstAddressCountryId();

        return self::getAll()->firstWhere('id', $userCountryId)
            ?? self::getAll()->firstWhere('code', SxGeo::getCountry())
            ?? self::getDefaultCountry();
    }
}
