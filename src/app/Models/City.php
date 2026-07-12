<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $country_id ID страны
 * @property string $name Название города
 * @property string $slug Slug города
 * @property string|null $catalog_title Загаловок в каталоге
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Country|null $country
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stock> $stocks
 */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * коллекция
     *
     * @return Relations\BelongsTo<Country, $this>
     */
    public function country(): Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Stocks
     *
     * @return Relations\HasMany<Stock, $this>
     */
    public function stocks(): Relations\HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['slug'] = Str::slug($value);
        $this->attributes['name'] = $value;
    }
}
