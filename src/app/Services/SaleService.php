<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SaleService
{
    /**
     * @var Sale
     */
    private $sale;

    /**
     * @var array
     */
    private $discounts = [];

    /**
     * @var boolean|null
     */
    private $hasSaleProductsInCart = null;

    public function __construct()
    {
        $this->sale = Sale::actual()->orderByDesc('id')->first();
        $this->prepareDiscounts();
    }

    /**
     * Prepare discounts list
     *
     * @return void
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
     *
     * @return boolean
     */
    protected function hasSale(): bool
    {
        return !empty($this->sale);
    }

    /**
     * Check nedding aplly for product
     *
     * @return boolean
     */
    protected function applyForOneProduct(): bool
    {
        switch ($this->sale->algorithm) {
            case $this->sale::ALGORITHM_FAKE:
            case $this->sale::ALGORITHM_SIMPLE:
                return true;

            case $this->sale::ALGORITHM_COUNT:
            case $this->sale::ALGORITHM_ASCENDING:
            default:
                return false;
        }
    }

    /**
     * Check special for algorithm conditions
     *
     * @return boolean
     */
    protected function checkSpecialConditions(float $price, float $oldPrice): bool
    {
        switch ($this->sale->algorithm) {
            case $this->sale::ALGORITHM_FAKE:
                return $price < $oldPrice;

            case $this->sale::ALGORITHM_SIMPLE:
            case $this->sale::ALGORITHM_COUNT:
            case $this->sale::ALGORITHM_ASCENDING:
            default:
                return true;
        }
    }

    /**
     * Check categories condition
     *
     * @param integer $categoryId
     * @return boolean
     */
    protected function checkCategory(int $categoryId): bool
    {
        return is_null($this->sale->categories) || !in_array($categoryId, $this->sale->categories);
    }

    /**
     * Check collections condition
     *
     * @param integer $collectionId
     * @return boolean
     */
    protected function checkCollection(int $collectionId): bool
    {
        return is_null($this->sale->collections) || !in_array($collectionId, $this->sale->collections);
    }

    /**
     * Check styles condition
     *
     * @param EloquentCollection $styles
     * @return boolean
     */
    protected function checkStyles(EloquentCollection $styles): bool
    {
        return is_null($this->sale->styles) || !empty(array_intersect($styles->modelKeys(), $this->sale->styles));
    }

    /**
     * Check season condition
     *
     * @param integer $seasonId
     * @return boolean
     */
    protected function checkSeason(int $seasonId): bool
    {
        return is_null($this->sale->seasons) || !in_array($seasonId, $this->sale->seasons);
    }

    /**
     * Check new item
     *
     * @param float $price
     * @param float $oldPrice
     * @return boolean
     */
    protected function checkNew(float $price, float $oldPrice): bool
    {
        return !$this->sale->only_new || $price > $oldPrice;
    }

    /**
     * Mix check sale conditions
     *
     * @param Product $product
     * @return boolean
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
     *
     * @param integer $index
     * @return float
     */
    protected function getDiscount(int $index = 0): float
    {
        return $this->discounts[$index] ?? $this->getOverflowDiscount();
    }

    /**
     * get overflow sale discount
     *
     * @param integer $index
     * @return float
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
     *
     * @param float $price
     * @param float $oldPrice
     * @param integer $index
     * @param integer $count
     * @return float
     */
    private function applySale(float $price, float $oldPrice, int $index = 0, int $count = 1): float
    {
        $baseDiscount = ($oldPrice - $price) / $oldPrice;

        switch ($this->sale->algorithm) {
            case $this->sale::ALGORITHM_FAKE:
                return $price;

            case $this->sale::ALGORITHM_SIMPLE:
                return $this->round($oldPrice * (1 - ($this->getDiscount() + $baseDiscount)));

            case $this->sale::ALGORITHM_COUNT:
                return $this->round($oldPrice * (1 - ($this->getDiscount(--$count) + $baseDiscount)));

            case $this->sale::ALGORITHM_ASCENDING:
                return $this->round($oldPrice * (1 - ($this->getDiscount($index) + $baseDiscount)));

            default:
                return $price;
        }
    }

    /**
     * Rounding to 5 kopecks
     *
     * @param float $num
     * @return float
     */
    protected function round(float $num)
    {
        return ceil($num * 20) / 20;
    }

    /**
     * Get sale data
     *
     * @param float $price
     * @param float $oldPrice
     * @param integer $index
     * @param integer $count
     * @return array
     */
    private function getSaleData(float $price, float $oldPrice, int $index = 0, int $count = 1): array
    {
        return [
            'price' => $this->applySale($price, $oldPrice, $index, $count),
            'label' => $this->sale->label_text
        ];
    }

    /**
     * Apply sale for Product model
     *
     * @param Product $product
     * @return void
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
     *
     * @return boolean
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
     *
     * @return boolean
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

        $products = $cart->items->map(function ($item, $key) {
            return $item->product;
        });
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
