<?php

namespace App\Models;

use App\Enums\Product\RatingFactor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $views_coefficient
 * @property int $carts_coefficient
 * @property int $purchases_coefficient
 * @property int $price_coefficient
 * @property int $discount_coefficient
 * @property int $category_up_coefficient
 * @property int $category_down_coefficient
 * @property int $season_coefficient
 * @property int $created_at_coefficient
 * @property int $product_up_coefficient
 * @property int $product_down_coefficient
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RatingAlgorithm extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'views_coefficient',
        'carts_coefficient',
        'purchases_coefficient',
        'price_coefficient',
        'discount_coefficient',
        'category_up_coefficient',
        'category_down_coefficient',
        'season_coefficient',
        'created_at_coefficient',
        'product_up_coefficient',
        'product_down_coefficient',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'views_coefficient' => 'integer',
            'carts_coefficient' => 'integer',
            'purchases_coefficient' => 'integer',
            'price_coefficient' => 'integer',
            'discount_coefficient' => 'integer',
            'category_up_coefficient' => 'integer',
            'category_down_coefficient' => 'integer',
            'season_coefficient' => 'integer',
            'created_at_coefficient' => 'integer',
            'product_up_coefficient' => 'integer',
            'product_down_coefficient' => 'integer',
        ];
    }

    /**
     * @return list<string>
     */
    public static function coefficientColumns(): array
    {
        return array_map(
            fn (RatingFactor $factor): string => $factor->coefficientColumn(),
            RatingFactor::cases()
        );
    }

    public function coefficientFor(RatingFactor $factor): int
    {
        $column = $factor->coefficientColumn();

        return (int)$this->{$column};
    }
}
