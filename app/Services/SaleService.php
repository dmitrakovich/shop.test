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

    public function __construct()
    {
        $this->sale = Sale::actual()->first();
        $this->prepareDiscounts();;
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
        return !$this->sale->only_new || $price >= $oldPrice;
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
     * @param integer $index
     * @param integer $count
     * @return float
     */
    protected function applySale(float $price, int $index = 0, int $count = 1): float
    {
        switch ($this->sale->algorithm) {
            case $this->sale::ALGORITHM_FAKE:
                return $price;

            case $this->sale::ALGORITHM_SIMPLE:
                return ceil($price * (1 - $this->getDiscount()));

            case $this->sale::ALGORITHM_COUNT:
                return ceil($price * (1 - $this->getDiscount($count)));

            case $this->sale::ALGORITHM_ASCENDING:
                return ceil($price * (1 - $this->getDiscount($index)));

            default:
                return $price;
        }
    }

    /**
     * Apply sale for Product model
     *
     * @param Product $product
     * @return void
     */
    public function applyForProduct(Product $product): void
    {
        if (
            $this->hasSale()
            && $this->applyForOneProduct()
            && $this->checkCategory($product->category_id)
            && $this->checkCollection($product->collection_id)
            && $this->checkStyles($product->styles)
            && $this->checkSeason($product->season_id)
            && $this->checkNew($product->price, $product->old_price)
        ) {
            $product->sale = [
                'price' => $this->applySale($product->price),
                'label' => $this->sale->label_text
            ];
        } else {
            $product->sale = [];
        }
    }





    public function applyForCart(Cart $cart)
    {
        // прогнать по условиям акции
        // упорядочить в зависимости от алгоритма
        // и добавить товарам соответствующие поля
    }
}
