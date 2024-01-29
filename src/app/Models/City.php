<?php

namespace App\Models;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Stock[] $stocks
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class City extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * коллекция
     */
    public function country(): Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Stocks
     */
    public function stocks(): Relations\HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['slug'] = Str::slug($value);
        $this->attributes['name'] = $value;
    }
}
