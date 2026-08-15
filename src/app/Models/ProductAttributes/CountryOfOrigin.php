<?php

namespace App\Models\ProductAttributes;

use Database\Factories\CountryOfOriginFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string $slug
 * @property string|null $seo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CountryOfOrigin extends Model
{
    /** @use HasFactory<CountryOfOriginFactory> */
    use HasFactory;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = $model->slug ?: Str::slug($model->name);
        });
    }
}
