<?php

namespace App\Services;

use App\Enums\ProductCarouselEnum;
use App\Facades\Currency;
use App\Models\Ads\ProductCarousel;
use App\Models\CartData;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @todo Refactor DRY !
 */
class SliderService
{
    /**
     * 1,5h
     */
    const CACHE_TTL = 1800;

    /**
     * Get simple product slider
     */
    public function getSimple(): array
    {
        $sliders = Cache::remember('simple_slider', self::CACHE_TTL, function () {
            $productCarousels = [];
            $carousels = ProductCarousel::ordered()
                ->where('is_imidj', false)
                ->whereNull('enum_type_id')
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
                        $query->onlyWithDiscount();
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
                                'id' => $product->id,
                                'sku' => $product->sku,
                                'full_name' => "{$product->category->name} {$product->brand->name}",
                                'sale_percentage' => $product->getSalePercentage(),
                                'is_new' => $product->isNew(),
                                'price_byn' => $product->getFinalPrice(),
                                'old_price_byn' => $product->getFinalOldPrice(),
                                'url' => $product->getUrl(),
                                'image' => $product->getFirstMediaUrl('default', 'catalog'),
                                'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                            ];
                        })->toArray(),
                    ];
                }
            }

            return $productCarousels;
        });

        foreach ($sliders as &$slider) {
            $this->setDataLayerForPage($slider['products']);
            $this->addConvertedAndFormattedPrice($slider['products']);
            $this->addFavorites($slider['products']);
        }

        return $sliders;
    }

    /**
     * Get imidj product slider
     */
    public function getImidj(): array
    {
        $slider = Cache::remember('imidj_slider', self::CACHE_TTL, function () {
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
                ->whereRelation('media', 'custom_properties', 'like', '%is_imidj%')
                ->sorting('rating')
                ->limit($slider->count)
                ->with(['media', 'category', 'brand', 'styles'])
                ->get();

            if (empty($products)) {
                return [];
            }

            return [
                'title' => $slider->title,
                'speed' => $slider->speed,
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'full_name' => "{$product->category->name} {$product->brand->name}",
                        'sale_percentage' => $product->getSalePercentage(),
                        'is_new' => $product->isNew(),
                        'price_byn' => $product->getFinalPrice(),
                        'old_price_byn' => $product->getFinalOldPrice(),
                        'url' => $product->getUrl(),
                        'image' => $product->getMedia('default', ['is_imidj' => true])
                            ->first()->getUrl('normal'),
                        'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                    ];
                })->toArray(),
            ];
        });

        $this->setDataLayerForPage($slider['products']);
        $this->addConvertedAndFormattedPrice($slider['products']);
        $this->addFavorites($slider['products']);

        return $slider;
    }

    /**
     * Get similar products slider
     */
    public function getSimilarProducts(int $productId): array
    {
        $cacheConfig = config('cache_config.product_carousel_similar_products');
        $slider = Cache::rememberForever($cacheConfig['key'], function () {
            return ProductCarousel::where('enum_type_id', ProductCarouselEnum::SIMILAR_PRODUCTS)
                ->first(['title', 'count', 'speed']);
        });
        if (empty($slider)) {
            return [];
        }
        $cacheConfig = config('cache_config.similar_products');
        $products = Cache::remember($cacheConfig['key'] . $productId, $cacheConfig['ttl'], function () use ($productId, $slider) {
            $attrs = ['sizes', 'colors', 'tags'];
            $product = Product::where('id', $productId)->withTrashed()->with($attrs)->first();
            do {
                $query = Product::where('id', '!=', $productId)
                    // ->when($product->category_id, fn ($query, $id) => $query->where('category_id', $id))
                    ->with(['media', 'category', 'brand', 'styles']);
                foreach ($attrs as $attr) {
                    $values = (!empty($product->{$attr}) && $product->{$attr} instanceof Collection) ? array_column($product->{$attr}->toArray(), 'id') : null;
                    if ($values) {
                        $query->whereHas($attr, function (Builder $q) use ($values) {
                            $q->where('id', $values);
                        });
                    }
                }
                $result = $query->limit($slider->count)->orderBy('rating', 'desc')->get();
                $recomended = isset($recomended) ? $recomended->merge($result) : $result;
                $recomended = $recomended->take($slider->count);
                array_pop($attrs);
            } while ($slider->count > count($recomended) && count($attrs));

            return $recomended->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'full_name' => $product->shortName(),
                    'sale_percentage' => $product->getSalePercentage(),
                    'is_new' => $product->isNew(),
                    'price_byn' => $product->getFinalPrice(),
                    'old_price_byn' => $product->getFinalOldPrice(),
                    'url' => $product->getUrl(),
                    'image' => $product->getFirstMediaUrl('default', 'catalog'),
                    'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                ];
            })->toArray();
        });
        $this->setDataLayerForPage($products);
        $this->addConvertedAndFormattedPrice($products);
        $this->addFavorites($products);

        return [
            'title' => $slider->title,
            'speed' => $slider->speed,
            'products' => $products,
        ];
    }

    /**
     * Get product group
     */
    public function getProductGroup(?int $productGroupId): array
    {
        if (!$productGroupId) {
            return [];
        }
        $cacheConfig = config('cache_config.product_carousel_product_group');
        $slider = Cache::rememberForever($cacheConfig['key'], function () {
            return ProductCarousel::where('enum_type_id', ProductCarouselEnum::PRODUCT_GROUP)
                ->first(['title', 'count', 'speed']);
        });
        if (empty($slider)) {
            return [];
        }
        $cacheConfig = config('cache_config.product_group');
        $products = Cache::remember($cacheConfig['key'] . $productGroupId, $cacheConfig['ttl'], function () use ($productGroupId, $slider) {
            $products = Product::where('product_group_id', $productGroupId)
                ->with(['media', 'category', 'brand', 'styles'])->limit($slider->count ?? 12)->get();

            return $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'full_name' => $product->shortName(),
                    'color_txt' => $product->color_txt,
                    'url' => $product->getUrl(),
                    'image' => $product->getFirstMediaUrl('default', 'catalog'),
                    'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                ];
            })->toArray();
        });
        $this->setDataLayerForPage($products);

        return [
            'title' => $slider->title,
            'speed' => $slider->speed,
            'products' => $products,
        ];
    }

    /**
     * Get final order page sliders
     */
    public function getFinalOrderPageSliders(Order $order): array
    {
        $finalUpsells = $this->getFinalUpsells($order);
        $finalSale = $this->getFinalSale();
        $result = [$finalUpsells, $finalSale];
        usort($result, function ($a, $b) {
            return ($a['sorting'] > $b['sorting']) ? 1 : -1;
        });

        return $result;
    }

    /**
     * Get final upsells slider
     */
    public function getFinalUpsells(Order $order): array
    {
        $cacheConfig = config('cache_config.final_upsells_slider_config');
        $slider = Cache::rememberForever($cacheConfig['key'], function () {
            return ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_UPSELLS)
                ->first(['title', 'count', 'speed', 'sorting', 'additional_settings']);
        });
        if (empty($slider)) {
            return [];
        }
        $cacheConfig = config('cache_config.product_group');
        $products = Cache::remember($cacheConfig['key'] . $order->id, ($cacheConfig['ttl'] ?? 1800), function () use ($order, $slider) {
            $order->load('items');
            $orderProductIds = $order->items->pluck('product_id')->toArray();
            $period = $slider->additional_settings['period'] ?? 90;
            $cartIds = CartData::select('cart_id')->whereIn('product_id', $orderProductIds)->where('created_at', '>=', Carbon::now()->subDays($period))->groupBy('cart_id')->pluck('cart_id')->toArray();
            $cartProductIds = CartData::select('product_id')->whereIn('cart_id', $cartIds)->groupBy('product_id')->pluck('product_id')->toArray();
            $favoriteIds = Favorite::withoutGlobalScopes()->select('device_id')->whereIn('product_id', $orderProductIds)->where('created_at', '>=', Carbon::now()->subDays($period))->groupBy('device_id')->pluck('device_id')->toArray();
            $favoriteProductIds = Favorite::select('product_id')->whereIn('device_id', $favoriteIds)->groupBy('product_id')->pluck('product_id')->toArray();
            $orderIds = OrderItem::select('order_id')->whereIn('product_id', $orderProductIds)->whereHas('order', fn ($query) => $query->where('created_at', '>=', Carbon::now()->subDays($period)))->groupBy('order_id')->pluck('order_id')->toArray();
            $orderProductIds = OrderItem::select('product_id')->whereIn('order_id', $orderIds)->groupBy('product_id')->pluck('product_id')->toArray();
            $productIds = array_slice(array_unique(array_merge($cartProductIds, $favoriteProductIds, $orderProductIds)), 0, 500);
            $products = Product::whereIn('id', $productIds)
                ->with(['media', 'category', 'brand'])->orderBy('rating', 'desc')->limit($slider->count ?? 12)->get();

            return $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'full_name' => $product->shortName(),
                    'sale_percentage' => $product->getSalePercentage(),
                    'is_new' => $product->isNew(),
                    'price_byn' => $product->getFinalPrice(),
                    'old_price_byn' => $product->getFinalOldPrice(),
                    'url' => $product->getUrl(),
                    'image' => $product->getFirstMediaUrl('default', 'catalog'),
                    'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                ];
            })->toArray();
        });
        $this->setDataLayerForPage($products);
        $this->addConvertedAndFormattedPrice($products);
        $this->addFavorites($products);

        return [
            'title' => $slider->title,
            'sorting' => $slider->sorting,
            'speed' => $slider->speed,
            'products' => $products,
        ];
    }

    /**
     * Get final sale slider
     */
    public function getFinalSale()
    {
        $cacheConfig = config('cache_config.final_sale_slider_config');
        Cache::forget($cacheConfig['key']);
        $slider = Cache::remember($cacheConfig['key'], ($cacheConfig['ttl'] ?? 1800), function () {
            $result = [];
            $carousel = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_SALE)
                ->first(['title', 'categories', 'count', 'speed', 'sorting', 'additional_settings']);

            $categories = [];
            foreach ($carousel->categories as $category_id) {
                $categories = array_merge(
                    $categories,
                    Category::getChildrenCategoriesIdsList($category_id)
                );
            }

            $products = Product::whereIn('category_id', $categories)
                ->onlyWithDiscount(0.03)
                ->sorting('rating')
                ->limit($carousel->count)
                ->with(['media', 'category', 'brand'])
                ->get();
            if (count($products)) {
                $result = [
                    'title' => $carousel->title,
                    'sorting' => $carousel->sorting,
                    'speed' => $carousel->speed,
                    'products' => $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'sku' => $product->sku,
                            'full_name' => "{$product->category->name} {$product->brand->name}",
                            'sale_percentage' => $product->getSalePercentage(),
                            'is_new' => $product->isNew(),
                            'price_byn' => $product->getFinalPrice(),
                            'old_price_byn' => $product->getFinalOldPrice(),
                            'url' => $product->getUrl(),
                            'image' => $product->getFirstMediaUrl('default', 'catalog'),
                            'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                        ];
                    })->toArray(),
                ];
            }

            return $result;
        });
        if (!empty($slider['products'])) {
            $this->setDataLayerForPage($slider['products']);
            $this->addConvertedAndFormattedPrice($slider['products']);
            $this->addFavorites($slider['products']);
        }

        return $slider;
    }

    /**
     * Get recent products slider
     */
    public function getRecentProducts(ProductService $productService): array
    {
        $cacheConfig = config('cache_config.product_carousel_recent_products');
        $slider = Cache::rememberForever($cacheConfig['key'], function () {
            return ProductCarousel::where('enum_type_id', ProductCarouselEnum::RECENT_PRODUCTS)
                ->first(['title', 'count', 'speed']);
        });
        if (empty($slider)) {
            return [];
        }
        $ids = $productService->getRecent();
        $products = Product::whereIn('id', $ids)->with(['media', 'category', 'brand'])->get();
        $flipIds = array_flip($ids);
        $resultProducts = [];
        if (empty($products)) {
            return [];
        }
        foreach ($products as $product) {
            $key = $flipIds[$product->id] ?? null;
            if ($key) {
                $resultProducts[$key] = [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'full_name' => $product->shortName(),
                    'sale_percentage' => $product->getSalePercentage(),
                    'is_new' => $product->isNew(),
                    'price_byn' => $product->getFinalPrice(),
                    'old_price_byn' => $product->getFinalOldPrice(),
                    'url' => $product->getUrl(),
                    'image' => $product->getFirstMediaUrl('default', 'catalog'),
                    'dataLayer' => GoogleTagManagerService::prepareProduct($product),
                ];
            }
        }
        krsort($resultProducts);
        $this->setDataLayerForPage($resultProducts);
        $this->addConvertedAndFormattedPrice($resultProducts);
        $this->addFavorites($resultProducts);

        return [
            'title' => $slider->title,
            'speed' => $slider->speed,
            'products' => $resultProducts,
        ];
    }

    /**
     * Add in products array converted and formatted price
     */
    protected function addConvertedAndFormattedPrice(array &$products): void
    {
        foreach ($products as &$product) {
            $product['formatted_price'] = Currency::convertAndFormat($product['price_byn']);
            $product['formatted_old_price'] = Currency::convertAndFormat($product['old_price_byn']);
        }
    }

    /**
     * Add favorites to products
     */
    protected function addFavorites(array &$products): void
    {
        $favorites = Favorite::whereIn('product_id', array_column($products, 'id'))
            ->pluck('product_id')->toArray();

        foreach ($products as &$product) {
            $product['favorite'] = in_array($product['id'], $favorites);
        }
    }

    /**
     * Prepare impressions data & call GTR Service
     */
    protected function setDataLayerForPage(array $products): void
    {
        $impressions = [];
        foreach ($products as $product) {
            $impressions[] = $product['dataLayer']->toArray();
        }
        GoogleTagManagerService::setEcommerceImpressions($impressions);
    }
}
