<?php

namespace App\Models\ProductAttributes;

use App\Contracts\Filterable;
use App\Facades\Currency;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property int $price
 * @property string $model
 *
 * @property-read \App\Models\Url|null $url
 */
class Price extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    /**
     * Return random id attribute
     */
    public function getIdAttribute(): string
    {
        return mt_rand();
    }

    /**
     * Return price value
     */
    public function getPriceAttribute(): int
    {
        return (int)Str::of($this->slug)->explode('-')->last();
    }

    /**
     * @param  \App\Models\Url[]  $values
     */
    public static function applyFilter(Builder $builder, array $values): Builder
    {
        foreach ($values as $url) {
            /** @var self $self */
            $self = $url->filters;
            $operator = str_starts_with($self->slug, 'price-from-') ? '>' : '<';
            $builder->where('price', $operator, $self->price);
        }

        return $builder;
    }

    /**
     * Generate filter badge name
     */
    public function getBadgeName(): string
    {
        $prefix = str_starts_with($this->slug, 'price-from-') ? 'От ' : 'До ';

        return $prefix . Currency::convertAndFormat($this->price);
    }
}
