<?php

namespace App\Services;

use App\Enums\Product\ProductLabel;
use App\Models\Product;
use App\Repositories\ProductRepository;
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

    private const int SIMILAR_PRODUCTS_COUNT = 12;

    public function __construct(private readonly ProductRepository $productRepository) {}

    /**
     * Get imidj products
     *
     * @return Collection|Product[]
     */
    public function getImidjProducts(): Collection
    {
        $productIds = Cache::remember('imidj_slider', self::CACHE_TTL, function () {
            return Product::query()
                ->where(function ($query) {
                    $query->where('label_id', '!=', ProductLabel::HIT)
                        ->orWhereNull('label_id');
                })
                ->orderBy('collection_id', 'desc')
                ->orderBy('rating', 'desc')
                ->limit(20)
                ->pluck('id')
                ->toArray();
        });

        return $this->productRepository->getForSliderByIds($productIds);
    }

    /**
     * Get trend products
     *
     * @return Collection|Product[]
     */
    public function getTrendProducts(): Collection
    {
        $productIds = Cache::remember('trend_slider', self::CACHE_TTL, function () {
            return Product::query()
                ->where('label_id', ProductLabel::HIT)
                ->orderBy('collection_id', 'desc')
                ->orderBy('rating', 'desc')
                ->limit(20)
                ->pluck('id')
                ->toArray();
        });

        return $this->productRepository->getForSliderByIds($productIds);
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
}
