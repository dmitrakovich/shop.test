<?php

namespace App\Models;

use App\Models\TagGroup;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\{
    Model,
    Relations
};

class Tag extends Model
{
    use AttributeFilterTrait;

    public $timestamps = false;

    /**
     * Теги
     */
    public function products()
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
