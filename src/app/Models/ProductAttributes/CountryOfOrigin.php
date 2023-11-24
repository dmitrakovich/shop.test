<?php

namespace App\Models\ProductAttributes;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $seo
 */
class CountryOfOrigin extends Model
{
    use AttributeFilterTrait, HasFactory;

    protected $guarded = ['id'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->slug = $model->slug ? $model->slug : Str::slug($model->name);
        });
    }
}
