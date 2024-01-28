<?php

namespace App\Models;

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
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Brand extends Model
{
    use AttributeFilterTrait;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected static function getRelationColumn()
    {
        return 'brand_id';
    }

    /**
     * Make dafault brand
     *
     * @return self
     */
    public static function getDefault()
    {
        return self::make([
            'id' => 57,
            'name' => 'BAROCCO',
            'slug' => 'barocco',
        ]);
    }
}
