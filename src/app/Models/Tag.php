<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $tag_group_id Номер группы
 * @property string $model
 *
 * @property-read \App\Models\TagGroup|null $group
 * @property-read \App\Models\Url|null $url
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Tag extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * Теги
     */
    public function products(): Relations\MorphToMany
    {
        return $this->morphToMany(Product::class, 'attribute', 'product_attributes');
    }

    /**
     * Tag group
     */
    public function group(): Relations\BelongsTo
    {
        return $this->belongsTo(TagGroup::class, 'tag_group_id');
    }
}
