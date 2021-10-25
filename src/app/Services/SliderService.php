<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ads\ProductCarousel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;

/**
 * @todo Refactor DRY
 */
class SliderService
{
    /**
     * Get simple product slider
     *
     * @return array
     */
    public function getSimple(): array
    {
        return Cache::remember('simple_slider', 1800, function () { // 0.5h
            $productCarousels = [];
            $carousels = ProductCarousel::ordered()
                ->where('is_imidj', false)
                ->get(['title', 'categories', 'only_sale', 'only_new', 'count', 'speed']);

            foreach ($carousels as $key => $carousel) {
                $categories = [];
                foreach ($carousel->categories as $category_id) {
                    $categories = array_merge(
                        $categories,
                        Category::getChildrenCategoriesIdsList($category_id)
                    );
                }

                $products = Product::whereIn('category_id', $categories)
                    ->when($carousel->only_sale, function ($query) {
                        $query->onlyWithSale();
                    })
                    ->when($carousel->only_new, function ($query) {
                        $query->onlyNew();
                    })
                    ->sorting('rating')
                    ->limit($carousel->count)
                    ->with(['media', 'category', 'brand'])
                    ->get();

                if (count($products)) {
                    $productCarousels[$key] = [
                        'title' => $carousel->title,
                        'speed' => $carousel->speed,
                        'products' => $products->map(function ($product) {
                            return [
                                'title' => $product->title,
                                'full_name' => "{$product->category->title} {$product->brand->name}",
                                'sale_percentage' => $product->getSalePercentage(),
                                'formatted_price' => $product->getFormattedPrice(),
                                'formatted_old_price' => $product->getFormattedOldPrice(),
                                'url' => $product->getUrl(),
                                'first_media' => $product->getFirstMedia()->getUrl('catalog')
                            ];
                        })->toArray()
                    ];
                }
            }
            return $productCarousels;
        });

    }

    /**
     * Get imidj product slider
     *
     * @return array
     */
    public function getImidj(): array
    {
        return Cache::remember('imidj_slider', 1800, function () { // 0.5h
            $slider = ProductCarousel::where('is_imidj', true)
                ->first(['title', 'categories', 'count', 'speed']);

            if (empty($slider)) {
                return [];
            }

            $categories = [];
            foreach ($slider->categories as $category_id) {
                $categories = array_merge(
                    $categories,
                    Category::getChildrenCategoriesIdsList($category_id)
                );
            }

            $products = Product::whereIn('category_id', $categories)
                ->whereHas('media', function (Builder $query) {
                    $query->where('custom_properties', 'like', '%is_imidj%');
                })
                ->sorting('rating')
                ->limit($slider->count)
                ->with(['media', 'category', 'brand'])
                ->get();

            if (empty($products)) {
                return [];
            }

            return [
                'title' => $slider->title,
                'speed' => $slider->speed,
                'products' => $products->map(function ($product) {
                    return [
                        'title' => $product->title,
                        'full_name' => "{$product->category->title} {$product->brand->name}",
                        'sale_percentage' => $product->getSalePercentage(),
                        'formatted_price' => $product->getFormattedPrice(),
                        'formatted_old_price' => $product->getFormattedOldPrice(),
                        'url' => $product->getUrl(),
                        'imidj_media' => $product->getMedia('default', ['is_imidj' => true])
                            ->first()->getUrl('normal')
                    ];
                })->toArray()
            ];
        });
    }
}
