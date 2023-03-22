<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;

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
