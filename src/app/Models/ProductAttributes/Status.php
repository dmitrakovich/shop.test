<?php

namespace App\Models\ProductAttributes;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $model
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Status extends Model
{
    use AttributeFilterTrait, HasFactory;

    protected $guarded = ['id'];

    /**
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
