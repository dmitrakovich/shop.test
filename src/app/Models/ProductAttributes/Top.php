<?php

namespace App\Models\ProductAttributes;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements Filterable<Model>
 */
class Top implements Filterable
{
    use AttributeFilterTrait;

    public static function applyFilter(Builder $builder, array $values): Builder
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

    public function url(): null
    {
        return null;
    }

    public function delete() {}
}
