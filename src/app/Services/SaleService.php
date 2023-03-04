<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Config;
use App\Models\Data\OrderData;
use App\Models\Data\SaleData;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SaleService
{
    private ?Sale $sale;

    private array $discounts = [];

    private ?bool $hasSaleProductsInCart = null;

    /**
     * Personal user's discount
     */
    private ?float $userDiscount = null;

    /**
     * Product review discount
     */
    private ?float $reviewDiscount = null;

    /**
     * SaleService construct
     */
    public function __construct()
    {
        $this->setSales();
        $this->prepareDiscounts();
    }

    /**
     * Set current sales
     */
    private function setSales(): void
    {
        $this->sale = Sale::actual()->orderByDesc('id')->first();

        $user = auth()->user();
        if ($user instanceof User) {
            $this->userDiscount = $user->group->discount;
            if ($user->hasReviewAfterOrder()) {
                $this->reviewDiscount = Config::findCacheable('feedback')['discount'];
            }
        }
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
     * Check has sale
     */
    protected function hasSale(): bool
    {
        return !empty($this->sale);
    }

    /**
     * Check user's discount
     */
    protected function hasUserSale(): bool
    {
        $addUserSale = $this->hasSale() ? $this->sale->add_client_sale : true;

        return $addUserSale && !is_null($this->userDiscount);
    }

    /**
     * Check user's discount
     */
    protected function hasReviewSale(): bool
    {
        $addReviewSale = $this->hasSale() ? $this->sale->add_review_sale : true;

        return $addReviewSale && !is_null($this->reviewDiscount);
    }

    /**
     * Check any sale
     */
    protected function hasAnySale(): bool
    {
        return $this->hasSale() || $this->hasUserSale() || $this->hasReviewSale();
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
    private function getSaleData(float $price, float $oldPrice, int $index = 0, int $count = 1): SaleData
    {
        $discountPrice = $this->applySale($price, $oldPrice, $index, $count);
        [$discount, $discountPercentage] = $this->getDiscountData($price, $discountPrice, $oldPrice, $index);

        return new SaleData(
            price: $discountPrice,
            discount: $discount,
            discount_percentage: $discountPercentage,
            label: $this->sale->label_text,
            end_datetime: $this->sale->end_datetime,
        );
    }

    /**
     * Calculate final general sale's discount data
     */
    private function getDiscountData(float $price, float $discountPrice, float $oldPrice, int $index): array
    {
        $isFakeAlgorithm = $this->sale->algorithm === $this->sale::ALGORITHM_FAKE;

        return $isFakeAlgorithm
            ? [$oldPrice - $price, floor((1 - ($price / $oldPrice)) * 100)]
            : [$price - $discountPrice, $this->getDiscount($index) * 100];
    }

    /**
     * Get user's sale data from auth user
     */
    private function getUserSaleData(float $price): SaleData
    {
        $discountPrice = $this->round($price - $price * $this->userDiscount / 100);

        return new SaleData(
            price: $discountPrice,
            discount: $price - $discountPrice,
            discount_percentage: $this->userDiscount,
            label: 'Скидка клиента'
        );
    }

    /**
     * Get user's review's sale data from auth user
     */
    private function getReviewSaleData(float $price): SaleData
    {
        $discountPrice = $this->round($price - $price * $this->reviewDiscount / 100);

        return new SaleData(
            price: $discountPrice,
            discount: $price - $discountPrice,
            discount_percentage: $this->reviewDiscount,
            label: 'Скидка за отзыв'
        );
    }

    /**
     * Remove user sale for some conditions
     */
    private function removeUserSale(): void
    {
        $this->userDiscount = null;
    }

    /**
     * Apply sale for Product model
     */
    public function applyForProduct(Product $product): void
    {
        $sales = [];
        $finalPrice = $product->price;

        if ($this->hasSale() && $this->applyForOneProduct() && $this->checkSaleConditions($product)) {
            $sale = $this->getSaleData($finalPrice, $product->getFixedOldPrice());
            $sales['general_sale'] = $sale;
            $finalPrice = $sale->price;
        }

        $this->applyUserSales($sales, $finalPrice);

        $product->setSales(['list' => $sales, 'final_price' => $finalPrice]);
    }

    /**
     * Apply user's sales to sales list by price
     */
    private function applyUserSales(array &$sales, float &$finalPrice): void
    {
        if ($this->hasUserSale()) {
            $sale = $this->getUserSaleData($finalPrice);
            $sales['user_sale'] = $sale;
            $finalPrice = $sale->price;
        }

        if ($this->hasReviewSale()) {
            $sale = $this->getReviewSaleData($finalPrice);
            $sales['review_sale'] = $sale;
            $finalPrice = $sale->price;
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

    /**
     * Apply to items in cart
     */
    public function applyToCart(Cart $cart): void
    {
        $this->hasSaleProductsInCart = false;

        if (!$this->hasAnySale()) {
            return;
        }

        $products = $cart->items->map(fn ($item) => $item->product);
        $products = $products->sortBy('price');

        $productSaleList = [];
        /** @var Product $product */
        foreach ($products as $product) {
            if ($this->hasSale() && $this->checkSaleConditions($product)) {
                $productSaleList[$product->id] = [
                    'price' => $product->price,
                    'oldPrice' => $product->getFixedOldPrice(),
                ];
                $this->hasSaleProductsInCart = true;
            }
        }
        $index = 0;
        foreach ($productSaleList as &$sale) {
            $sale = $this->getSaleData($sale['price'], $sale['oldPrice'], $index++, count($productSaleList));
        }

        foreach ($cart->items as $item) {
            $generalSale = $productSaleList[$item->product->id] ?? null;
            $sales = $generalSale ? ['general_sale' => $generalSale] : [];
            $finalPrice = $generalSale ? $generalSale->price : $item->product->price;

            $this->applyUserSales($sales, $finalPrice);
            $item->product->setSales(['list' => $sales, 'final_price' => $finalPrice]);
        }
    }

    /**
     * Apply sales to order
     */
    public function applyToOrder(Cart $cart, OrderData $orderData)
    {
        if ($orderData->paymentMethod->instance === 'Installment') {
            $this->removeUserSale();
        }

        $this->applyToCart($cart);
    }
}
