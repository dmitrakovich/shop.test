<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Collection extends Model
{
    use AttributeFilterTrait, HasFactory;

    protected static function getRelationColumn()
    {
        return 'collection_id';
    }
}
