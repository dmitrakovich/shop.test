<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $one_c_id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @property-read \App\Models\Url|null $url
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Brand extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    protected static function getRelationColumn(): string
    {
        return 'brand_id';
    }

    /**
     * Make default brand
     */
    public static function getDefault(): self
    {
        return self::make([
            'id' => 57,
            'name' => 'BAROCCO',
            'slug' => 'barocco',
        ]);
    }
}
