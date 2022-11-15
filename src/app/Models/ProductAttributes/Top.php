<?php

namespace App\Models\ProductAttributes;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;

class Top
{
    use AttributeFilterTrait;

    /**
     * @param  Builder  $builder
     * @param  array  $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        return $builder->whereNotIn('id', array_column($values, 'model_id'));
    }

    /**
     * Mark filter as invisible
     */
    public function isInvisible(): bool
    {
        return true;
    }
}
