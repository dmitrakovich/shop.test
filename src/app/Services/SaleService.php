<?php

namespace App\Services;

use App\Enums\Promo\SaleAlgorithm;
use App\Facades\Currency;
use App\Models\Cart;
use App\Models\Config;
use App\Models\Data\OrderData;
use App\Models\Data\SaleData;
use App\Models\Product;
use App\Models\Promo\Sale;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class SaleService
{
    /**
     * Sale model
     */
    private ?Sale $sale;

    /**
     * Key for general sale
     */
    const GENERAL_SALE_KEY = 'general_sale';

    /**
     * Key for user sale
     */
    const USER_SALE_KEY = 'user_sale';

    /**
     * Key for review sale
     */
    const REVIEW_SALE_KEY = 'review_sale';

    /**
     * List of discounts
     */
    private array $discounts = [];

    /**
     * Cache for checked products
     */
    private array $productHasSale = [];

    /**
     * Sign of the presence in the basket of goods with a discount
     */
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
     * List of disabled sales
     */
    private array $disabled = [];

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
                $this->reviewDiscount = $this->getReviewDiscount();
            }
        }
    }

    /**
     * Prepare discounts list
     */
    protected function prepareDiscounts(): void
    {
        if ($this->hasSale()) {
            $this->discounts = array_filter(array_map('trim', explode(',', $this->sale->sale_percentage)));
        }
    }

    /**
     * Check has sale
     */
    protected function hasSale(): bool
    {
        return !empty($this->sale) && empty($this->disabled[self::GENERAL_SALE_KEY]);
    }

    /**
     * Check user's discount
     */
    protected function hasUserSale(Product $product): bool
    {
        if (isset($this->disabled[self::USER_SALE_KEY]) || $product->old_price > $product->price) {
            return false;
        }
        $addUserSale = $this->productHasGeneralSale($product) ? $this->sale->add_client_sale : true;

        return $addUserSale && !is_null($this->userDiscount);
    }

    /**
     * Get review discount sum by current currency
     */
    protected function getReviewDiscount(): ?float
    {
        $currency = Currency::getCurrentCurrency();
        $discount = Config::findCacheable('feedback')['discount'][$currency->code] ?? 0;

        return $discount ? $discount / $currency->rate : null;
    }

    /**
     * Check user's discount
     */
    protected function hasReviewSale(Product $product): bool
    {
        if (isset($this->disabled[self::REVIEW_SALE_KEY])) {
            return false;
        }
        $addReviewSale = $this->productHasGeneralSale($product) ? $this->sale->add_review_sale : true;

        return $addReviewSale && !is_null($this->reviewDiscount);
    }

    /**
     * Check needing apply for product
     */
    protected function applyForOneProduct(): bool
    {
        return match ($this->sale->algorithm) {
            SaleAlgorithm::FAKE, SaleAlgorithm::SIMPLE => true,
            default => false,
        };
    }

    /**
     * Check special for algorithm conditions
     */
    protected function checkSpecialConditions(float $price, float $oldPrice): bool
    {
        return match ($this->sale->algorithm) {
            SaleAlgorithm::FAKE => $price < $oldPrice,
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
     * check all conditions related to discounts
     */
    protected function checkDiscountConditions(float $price, float $oldPrice): bool
    {
        return (!$this->sale->only_new || $price > $oldPrice)
            && (!$this->sale->only_discount || $oldPrice > $price);
    }

    /**
     * Check if sale is applied to a product
     */
    protected function productHasGeneralSale(Product $product): bool
    {
        return $this->hasSale() && $this->checkSaleConditions($product);
    }

    /**
     * Check the terms of sale for a product using the cache
     */
    protected function checkSaleConditions(Product $product): bool
    {
        return $this->productHasSale[$product->id] ??= $this->_checkSaleConditions($product);
    }

    /**
     * Mix check sale conditions
     */
    protected function _checkSaleConditions(Product $product): bool
    {
        return $this->checkSpecialConditions($product->price, $product->old_price)
            && $this->checkCategory($product->category_id)
            && $this->checkCollection($product->collection_id)
            && $this->checkStyles($product->styles)
            && $this->checkSeason($product->season_id)
            && $this->checkDiscountConditions($product->price, $product->old_price);
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
        if ($this->sale->algorithm->isCount()) {
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
            SaleAlgorithm::FAKE => $price,
            SaleAlgorithm::SIMPLE => $this->round($oldPrice * (1 - ($this->getDiscount() + $baseDiscount))),
            SaleAlgorithm::COUNT => $this->round($oldPrice * (1 - ($this->getDiscount(--$count) + $baseDiscount))),
            SaleAlgorithm::ASCENDING => $this->round($oldPrice * (1 - ($this->getDiscount($index) + $baseDiscount))),
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
        return $this->sale->algorithm->isFake()
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
        return new SaleData(
            price: $price - $this->reviewDiscount,
            discount: $this->reviewDiscount,
            discount_percentage: $this->round($this->reviewDiscount * 100 / $price),
            label: 'Скидка за отзыв'
        );
    }

    /**
     * Disable user sale for some conditions
     */
    public function disableUserSale(): void
    {
        $this->disabled[self::USER_SALE_KEY] = 1;
    }

    /**
     * Enable user sale after disable
     */
    public function enableUserSale(): void
    {
        unset($this->disabled[self::USER_SALE_KEY]);
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
            $sales[self::GENERAL_SALE_KEY] = $sale;
            $finalPrice = $sale->price;
        }

        $this->applyUserSales($product, $sales, $finalPrice);

        $product->setSales($sales, $finalPrice);
    }

    /**
     * Apply user's sales to sales list by price
     */
    private function applyUserSales(Product $product, array &$sales, float &$finalPrice): void
    {
        if ($this->hasUserSale($product)) {
            $sale = $this->getUserSaleData($finalPrice);
            $sales[self::USER_SALE_KEY] = $sale;
            $finalPrice = $sale->price;
        }

        if ($this->hasReviewSale($product)) {
            $sale = $this->getReviewSaleData($finalPrice);
            $sales[self::REVIEW_SALE_KEY] = $sale;
            $finalPrice = $sale->price;
        }
    }

    /**
     * Check has delivery with fitting for sale
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
        $productSaleList = [];
        $this->hasSaleProductsInCart = false;

        $products = $cart->availableItems()->map(fn ($item) => $item->product);
        $products = $products->sortBy('price');

        if ($this->hasSale()) {
            /** @var Product $product */
            foreach ($products as $product) {
                if ($this->checkSaleConditions($product)) {
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
        }

        foreach ($cart->availableItems() as $item) {
            $generalSale = $productSaleList[$item->product->id] ?? null;
            $sales = $generalSale ? [self::GENERAL_SALE_KEY => $generalSale] : [];
            $finalPrice = $generalSale ? $generalSale->price : $item->product->price;

            $this->applyUserSales($item->product, $sales, $finalPrice);
            $item->product->setSales($sales, $finalPrice);
        }
    }

    /**
     * Apply sales to order
     */
    public function applyToOrder(Cart $cart, OrderData $orderData)
    {
        if ($orderData->paymentMethod->instance === 'Installment') {
            $this->disableUserSale();
        }

        $this->applyToCart($cart);
    }
}
