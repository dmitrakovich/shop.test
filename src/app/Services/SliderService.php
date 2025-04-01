<?php

namespace App\Services;

use App\Enums\ProductCarouselEnum;
use App\Facades\Currency;
use App\Models\Ads\ProductCarousel;
use App\Models\CartData;
use App\Models\Favorite;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use App\Repositories\ProductRepository;
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

    private const SIMILAR_PRODUCTS_COUNT = 12;

    public function __construct(private readonly ProductRepository $productRepository) {}

    /**
     * Get simple products for sliders
     */
    public function getSimpleProducts(): array
    {
        $slidersProducts = Cache::remember('simple_slider', self::CACHE_TTL, function () {
            return ProductCarousel::getSimpleCarousels()->map(
                fn (ProductCarousel $carousel) => Product::query()
                    ->whereIn('category_id', $carousel->getCategoryIds())
                    ->when($carousel->only_sale, function ($query) {
                        $query->onlyWithDiscount();
                    })
                    ->when($carousel->only_new, function ($query) {
                        $query->onlyNew();
                    })
                    ->sorting('rating')
                    ->limit($carousel->count)
                    ->pluck('id')
            )->toArray();
        });

        $sliders = [];
        foreach (array_filter($slidersProducts) as $productIds) {
            $sliders[] = $this->productRepository->getForSliderByIds($productIds);
        }

        return $sliders;
    }

    /**
     * Get formatted simple product sliders
     */
    public function getFormattedSimple(): array
    {
        $sliders = $this->getSimpleProducts();

        return [
            [
                'title' => 'ХИТЫ ПРОДАЖ',
                'products' => $this->formatProducts($sliders[0]),
            ],
            [
                'title' => 'Вечерняя обувь',
                'products' => $this->formatProducts($sliders[1]),
            ],
        ];
    }

    /**
     * Get imidj products
     *
     * @return Collection|Product[]
     */
    public function getImidjProducts(): Collection
    {
        $productIds = Cache::remember('imidj_slider', self::CACHE_TTL, function () {
            $slider = ProductCarousel::getImidjCarousel();
            if (!$slider) {
                return [];
            }

            return Product::query()
                ->whereIn('category_id', $slider->getCategoryIds())
                ->whereRelation('media', 'custom_properties', 'like', '%is_imidj%')
                ->sorting('rating')
                ->limit($slider->count)
                ->pluck('id')
                ->toArray();
        });

        return $this->productRepository->getForSliderByIds($productIds);
    }

    public function getFormattedImidj(): array
    {
        return [
            'title' => 'Популярное',
            'products' => $this->formatProducts($this->getImidjProducts(), isImidj: true),
        ];
    }

    /**
     * Get similar products slider
     *
     * @return Collection|Product[]
     */
    public function getSimilarProducts(int $productId): Collection
    {
        $cacheConfig = config('cache_config.similar_products');
        $productIds = Cache::remember($cacheConfig['key'] . $productId, $cacheConfig['ttl'], function () use ($productId) {
            $attrs = ['sizes', 'colors', 'tags'];
            $product = Product::query()->where('id', $productId)->withTrashed()->with($attrs)->first();
            do {
                $query = Product::query()->where('id', '!=', $productId)
                    ->with(['media', 'category', 'brand', 'styles']);
                foreach ($attrs as $attr) {
                    $values = (!empty($product->{$attr}) && $product->{$attr} instanceof Collection) ? array_column($product->{$attr}->toArray(), 'id') : null;
                    if ($values) {
                        $query->whereHas($attr, function (Builder $q) use ($values) {
                            $q->where('id', $values);
                        });
                    }
                }
                $result = $query->limit(self::SIMILAR_PRODUCTS_COUNT)->orderBy('rating', 'desc')->get();
                $recommended = isset($recommended) ? $recommended->merge($result) : $result;
                $recommended = $recommended->take(self::SIMILAR_PRODUCTS_COUNT);
                array_pop($attrs);
            } while (count($recommended) < self::SIMILAR_PRODUCTS_COUNT && count($attrs));

            return $recommended->pluck('id')->toArray();
        });

        return $this->productRepository->getForSliderByIds($productIds);
    }

    public function getFormattedSimilarProducts(int $productId): array
    {
        return [
            'title' => 'Похожие товары',
            'speed' => 3000,
            'products' => $this->formatProducts($this->getSimilarProducts($productId)),
        ];
    }

    /**
     * @param  Collection|Product[]  $productsCollection
     */
    private function formatProducts(Collection $productsCollection, bool $isImidj = false): array
    {
        $products = $productsCollection->map(fn (Product $product) => [
            'id' => $product->id,
            'sku' => $product->sku,
            'full_name' => $product->extendedName(),
            'color_txt' => $product->color_txt,
            'sale_percentage' => $product->getSalePercentage(),
            'is_new' => $product->isNew(),
            'favorite' => $product->isFavorite(),
            'price_byn' => $product->getFinalPrice(),
            'old_price_byn' => $product->getFinalOldPrice(),
            'formatted_price' => $product->getFormattedPrice(),
            'formatted_old_price' => $product->getFormattedOldPrice(),
            'url' => $product->getUrl(),
            'image' => $isImidj ? $product->getFirstImidjMediaUrl() : $product->getFirstCatalogMediaUrl(),
            'dataLayer' => GoogleTagManagerService::prepareProduct($product),
        ])->toArray();

        $this->setDataLayerForPage($products);

        return $products;
    }

    /**
     * Get formatted group products
     */
    public function getFormattedProductGroup(Product $product): array
    {
        return [
            'title' => 'Похожие товары',
            'speed' => 3000,
            'products' => $this->formatProducts($product->productsFromGroup),
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
            $favoriteIds = Favorite::query()->select('device_id')->whereIn('product_id', $orderProductIds)->where('created_at', '>=', Carbon::now()->subDays($period))->groupBy('device_id')->pluck('device_id')->toArray();
            $favoriteProductIds = Favorite::query()->select('product_id')->whereIn('device_id', $favoriteIds)->groupBy('product_id')->pluck('product_id')->toArray();
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
            $carousel = ProductCarousel::getFinalSaleCarousel();
            if (!$carousel) {
                return [];
            }

            $products = Product::query()
                ->whereIn('category_id', $carousel->getCategoryIds())
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
        $ids = $productService->getRecent();
        $products = Product::query()
            ->with(['media', 'category', 'brand'])
            ->whereIn('id', $ids)
            ->get();
        $flipIds = array_flip($ids);
        $resultProducts = [];
        if ($products->isEmpty()) {
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
            'title' => 'Недавно просмотренные товары',
            'speed' => 3000,
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
        $favorites = Favorite::forUser()
            ->whereIn('product_id', array_column($products, 'id'))
            ->pluck('product_id')
            ->toArray();

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
