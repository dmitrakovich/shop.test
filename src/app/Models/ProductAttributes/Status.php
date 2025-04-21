<?php

namespace App\Models\ProductAttributes;

use App\Contracts\Filterable;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @property-read \App\Models\Url|null $url
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Status extends Model implements Filterable
{
    use AttributeFilterTrait;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    public static function applyFilter(Builder $builder, array $values): Builder
    {
        foreach ($values as $slug => $urlModel) {
            switch ($slug) {
                case 'st-new':
                    $builder->where('old_price', 0);
                    break;

                case 'st-sale':
                    $builder->whereColumn('price', '<', 'old_price');
                    break;

                case 'promotion':
                    Promotion::getProductsForAllActiveSales($builder);
                    break;
            }
        }

        return $builder;
    }

    /**
     * Prepare for page title
     */
    public function getForTitle(): string
    {
        return match ($this->slug) {
            'st-new' => '- новинки!',
            'st-sale' => 'на распродаже!',
            'promotion' => 'на акции',
        };
    }
}
