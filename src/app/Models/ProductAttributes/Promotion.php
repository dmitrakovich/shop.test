<?php

namespace App\Models\ProductAttributes;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Sale;
use App\Models\Season;
use App\Models\Style;
use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for all promotions actions
 */
class Promotion extends Model
{
    use HasFactory, AttributeFilterTrait;

    /**
     * @param  Builder  $builder
     * @param  array  $values
     * @return Builder
     */
    public static function applyFilter(Builder $builder, array $values)
    {
        foreach ($values as $slug => $urlModel) {
            return match ($slug) {
                'promotion' => self::getProductsForAllActiveSales($builder),
            };
        }
    }

    /**
     * Get all sales & apply sales config to products
     */
    public static function getProductsForAllActiveSales(Builder $builder): Builder
    {
        $builder->where(function ($query) {
            Sale::query()->actual()->get()->each(function (Sale $sale) use ($query) {
                $query->orWhere(function ($query) use ($sale) {
                    if (!empty($sale->categories)) {
                        $categories = [];
                        foreach ($sale->categories as $categoryId) {
                            $categories = array_merge(
                                $categories,
                                Category::getChildrenCategoriesIdsList($categoryId)
                            );
                        }
                        $query->whereIn('category_id', $categories);
                    }

                    if (!empty($sale->collections)) {
                        Collection::applyFilter($query, $sale->collections);
                    }

                    if (!empty($sale->styles)) {
                        Style::applyFilter($query, $sale->styles);
                    }

                    if (!empty($sale->seasons)) {
                        Season::applyFilter($query, $sale->seasons);
                    }

                    if ($sale->only_new) {
                        $query->onlyNew();
                    }

                    if ($sale->algorithm === Sale::ALGORITHM_FAKE) {
                        $query->onlyWithSale();
                    }
                });
            });
        });

        return $builder;
    }
}
