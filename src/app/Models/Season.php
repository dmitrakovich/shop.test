<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
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
class Season extends Model
{
    use AttributeFilterTrait;

    protected static function getRelationColumn()
    {
        return 'season_id';
    }
}
