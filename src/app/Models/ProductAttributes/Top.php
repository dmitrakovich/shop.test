<?php

namespace App\Models\ProductAttributes;

use Illuminate\Database\Eloquent\Builder;

class Top
{
    /**
     * @param Builder $builder
     * @param array $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        return $builder->whereNotIn('id', $values);
    }
}
