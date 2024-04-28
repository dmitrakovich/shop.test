<?php

namespace App\Models\ProductAttributes;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Promo\Sale;
use App\Models\Season;
use App\Models\Style;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Promotion extends Model
{
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

                    if ($sale->algorithm->isFake()) {
                        $query->onlyWithDiscount();
                    }
                });
            });
        });

        return $builder;
    }
}
