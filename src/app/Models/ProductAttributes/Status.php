<?php

namespace App\Models\ProductAttributes;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProductAttributes\Promotion;

/**
 * Product status class
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class Status extends Model
{
    use HasFactory, AttributeFilterTrait;
    protected $guarded = ['id'];

    /**
     * @param Builder $builder
     * @param array $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
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
