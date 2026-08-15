<?php

namespace App\Models\ProductAttributes;

use App\Contracts\Filterable;
use App\Facades\Currency;
use App\Models\Url;
use App\Traits\Filterable as FilterableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $slug
 * @property int $price
 *
 * @property-read \App\Models\Url|null $url
 *
 * @implements Filterable<Price>
 */
class Price extends Model implements Filterable
{
    use FilterableTrait;

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

    public static function elasticField(): ?string
    {
        return 'price';
    }

    /**
     * @param  array<string, Url>  $values
     * @return list<array<string, mixed>>
     */
    public static function elasticFilterClauses(array $values): array
    {
        $priceRange = [];
        foreach ($values as $slug => $url) {
            $price = $url->filters;
            if (!$price instanceof self) {
                continue;
            }
            if (str_starts_with((string)$slug, 'price-from-')) {
                $priceRange['gt'] = (float)$price->price;
            } else {
                $priceRange['lt'] = (float)$price->price;
            }
        }

        if ($priceRange === []) {
            return [];
        }

        return [['range' => [(string)static::elasticField() => $priceRange]]];
    }

    /**
     * Human-readable price bound for SEO titles.
     */
    public function forTitle(): string
    {
        $prefix = str_starts_with($this->slug, 'price-from-') ? 'От ' : 'До ';

        return $prefix . Currency::convertAndFormat($this->price);
    }
}
