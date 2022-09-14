<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SaleService
{
    private ?Sale $sale;

    private array $discounts = [];

    private ?bool $hasSaleProductsInCart = null;

    public function __construct()
    {
        $this->sale = Sale::actual()->orderByDesc('id')->first();
        $this->prepareDiscounts();
    }

    /**
     * Prepare discounts list
     */
    protected function prepareDiscounts(): void
    {
        if ($this->hasSale()) {
            $this->discounts = array_filter(array_map('trim', explode(',', $this->sale->sale)));
        }
    }

    /**
     * Return current sale
     *
     * @return Sale|null
     */
    public function getCurrentSale()
    {
        return $this->sale;
    }

    /**
     * Check has sale
     */
    protected function hasSale(): bool
    {
        return !empty($this->sale);
    }

    /**
     * Check nedding aplly for product
     */
    protected function applyForOneProduct(): bool
    {
        return match ($this->sale->algorithm) {
            $this->sale::ALGORITHM_FAKE, $this->sale::ALGORITHM_SIMPLE => true,
            default => false,
        };
    }

    /**
     * Check special for algorithm conditions
     */
    protected function checkSpecialConditions(float $price, float $oldPrice): bool
    {
        return match ($this->sale->algorithm) {
            $this->sale::ALGORITHM_FAKE => $price < $oldPrice,
            default => true,
        };
    }

    /**
     * Check categories condition
     */
    protected function checkCategory(int $categoryId): bool
    {
        return is_null($this->sale->categories) || in_array($categoryId, $this->sale->categories);
    }

    /**
     * Check collections condition
     */
    protected function checkCollection(int $collectionId): bool
    {
        return is_null($this->sale->collections) || in_array($collectionId, $this->sale->collections);
    }

    /**
     * Check styles condition
     */
    protected function checkStyles(EloquentCollection $styles): bool
    {
        return is_null($this->sale->styles) || !empty(array_intersect($styles->modelKeys(), $this->sale->styles));
    }

    /**
     * Check season condition
     */
    protected function checkSeason(int $seasonId): bool
    {
        return is_null($this->sale->seasons) || in_array($seasonId, $this->sale->seasons);
    }

    /**
     * Check new item
     */
    protected function checkNew(float $price, float $oldPrice): bool
    {
        return !$this->sale->only_new || $price > $oldPrice;
    }

    /**
     * Mix check sale conditions
     */
    protected function checkSaleConditions(Product $product): bool
    {
        return $this->checkSpecialConditions($product->price, $product->old_price)
            && $this->checkCategory($product->category_id)
            && $this->checkCollection($product->collection_id)
            && $this->checkStyles($product->styles)
            && $this->checkSeason($product->season_id)
            && $this->checkNew($product->price, $product->old_price);
    }

    /**
     * get sale discount
     */
    protected function getDiscount(int $index = 0): float
    {
        return $this->discounts[$index] ?? $this->getOverflowDiscount();
    }

    /**
     * get overflow sale discount
     */
    protected function getOverflowDiscount(): float
    {
        if ($this->sale->algorithm == $this->sale::ALGORITHM_COUNT) {
            return (float)end($this->discounts);
        } else {
            return 0;
        }
    }

    /**
     * Apply sale
     */
    private function applySale(float $price, float $oldPrice, int $index = 0, int $count = 1): float
    {
        $baseDiscount = ($oldPrice - $price) / $oldPrice;

        return match ($this->sale->algorithm) {
            $this->sale::ALGORITHM_FAKE => $price,
            $this->sale::ALGORITHM_SIMPLE => $this->round($oldPrice * (1 - ($this->getDiscount() + $baseDiscount))),
            $this->sale::ALGORITHM_COUNT => $this->round($oldPrice * (1 - ($this->getDiscount(--$count) + $baseDiscount))),
            $this->sale::ALGORITHM_ASCENDING => $this->round($oldPrice * (1 - ($this->getDiscount($index) + $baseDiscount))),
            default => $price,
        };
    }

    /**
     * Rounding to 5 kopecks
     *
     * @return float
     */
    protected function round(float $num)
    {
        return ceil($num * 20) / 20;
    }

    /**
     * Get sale data
     */
    private function getSaleData(float $price, float $oldPrice, int $index = 0, int $count = 1): array
    {
        return [
            'price'        => $this->applySale($price, $oldPrice, $index, $count),
            'label'        => $this->sale->label_text,
            'end_datetime' => $this->sale->end_datetime ?? null
        ];
    }

    /**
     * Apply sale for Product model
     */
    public function applyForProduct(Product $product): void
    {
        if ($this->hasSale() && $this->applyForOneProduct() && $this->checkSaleConditions($product)) {
            $product->sale = $this->getSaleData($product->price, $product->getFixedOldPrice());
        } else {
            $product->sale = [];
        }
    }

    /**
     * Check has delivery with fittng for sale
     */
    public function hasFitting(): bool
    {
        if (is_null($this->hasSaleProductsInCart)) {
            throw new \Exception('First need apply sale for cart');
        }
        return !$this->hasSaleProductsInCart || $this->sale->has_fitting;
    }

    /**
     * Check has payment with installment for sale
     */
    public function hasInstallment(): bool
    {
        if (is_null($this->hasSaleProductsInCart)) {
            throw new \Exception('First need apply sale for cart');
        }
        return !$this->hasSaleProductsInCart || $this->sale->has_installment;
    }

    public function applyForCart(Cart $cart)
    {
        $this->hasSaleProductsInCart = false;

        if (!$this->hasSale()) return;

        $products = $cart->items->map(fn($item, $key) => $item->product);
        $products = $products->sortBy('price');

        $productSaleList = [];
        foreach ($products as $product) {
            if ($this->checkSaleConditions($product)) {
                $productSaleList[$product->id] = [
                    'price' => $product->price,
                    'oldPrice' => $product->getFixedOldPrice()
                ];
                $this->hasSaleProductsInCart = true;
            }
        }
        $index = 0;
        foreach ($productSaleList as &$sale) {
            $sale = $this->getSaleData($sale['price'], $sale['oldPrice'], $index++, count($productSaleList));
        }

        foreach ($cart->items as $item) {
            $item->product->sale = $productSaleList[$item->product->id] ?? [];
        }
    }
}
