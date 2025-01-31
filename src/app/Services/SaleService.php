<?php

namespace App\Services;

use App\Enums\Promo\SaleAlgorithm;
use App\Facades\Cart as CartFacade;
use App\Facades\Currency;
use App\Models\Cart;
use App\Models\Config;
use App\Models\Data\OrderData;
use App\Models\Data\SaleData;
use App\Models\Product;
use App\Models\Promo\Promocode;
use App\Models\Promo\Sale;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;

class SaleService
{
    /**
     * Sale model
     */
    private ?Sale $sale;

    /**
     * Key for product discount as sale
     */
    const PRODUCT_DISCOUNT = 'product_discount';

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
     * The key for cookie with pending promocode.
     */
    const COOKIE_KEY_FOR_PENDING_PROMOCODE = 'pending_promocode';

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
        $this->setUp();
    }

    /**
     * Set up the sales and prepare discounts.
     */
    private function setUp(): void
    {
        $this->setSales();
        $this->prepareDiscounts();
    }

    /**
     * Set current sales
     */
    private function setSales(): void
    {
        $this->setAuthUserSales();

        if (!isset($this->sale)) {
            $this->sale = Sale::actual()->orderByDesc('id')->first();
        }
    }

    /**
     * Set the sales information for the authenticated user.
     */
    private function setAuthUserSales(): void
    {
        $user = auth()->user();
        if (!$user instanceof User) {
            return;
        }

        $this->userDiscount = $user->group->discount;
        if ($user->hasReviewAfterOrder()) {
            $this->reviewDiscount = $this->getReviewDiscount();
        }

        if (!$promocode = $user->cart?->promocode) {
            return;
        }
        if ($promocode->isExpiredForUser()) {
            CartFacade::clearPromocode();
        } else {
            $this->sale = $promocode->getSaleForUser();
        }
    }

    /**
     * Prepare discounts list
     */
    protected function prepareDiscounts(): void
    {
        // todo: use VO for 2 types of discount
        // ? example: https://shopify.dev/docs/api/admin-rest/2024-04/resources/pricerule#get-price-rules-price-rule-id
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
        return true;
        // return match ($this->sale->algorithm) {
        //     SaleAlgorithm::FAKE, SaleAlgorithm::SIMPLE => true,
        //     default => false,
        // };
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
            SaleAlgorithm::ASCENDING, SaleAlgorithm::DESCENDING => $this->round($oldPrice * (1 - ($this->getDiscount($index) + $baseDiscount))),
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
    private function getReviewSaleData(float $price, float $oldPrice): SaleData
    {
        return new SaleData(
            price: $price - $this->reviewDiscount,
            discount: $this->reviewDiscount,
            discount_percentage: $this->round($this->reviewDiscount * 100 / $oldPrice),
            label: 'Скидка за отзыв'
        );
    }

    /**
     * Get user's review's sale data from auth user
     */
    private function getProductDiscountAsSale(Product $product): SaleData
    {
        $oldPrice = $product->getFixedOldPrice();
        $discount = $oldPrice - $product->price;

        return new SaleData(
            price: $oldPrice,
            discount: $discount,
            discount_percentage: $this->round($discount * 100 / $oldPrice),
            label: 'Распродажа'
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

        if (!$this->hasFakeSale()) {
            $sales[self::PRODUCT_DISCOUNT] = $this->getProductDiscountAsSale($product);
        }

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
            $sale = $this->getReviewSaleData($finalPrice, $product->getFixedOldPrice());
            $sales[self::REVIEW_SALE_KEY] = $sale;
            $finalPrice = $sale->price;
        }
    }

    /**
     * Check if fitting is available
     */
    public function hasFitting(): bool
    {
        return !$this->hasSaleProductsInCart() || $this->sale->has_fitting;
    }

    /**
     * Check if installment is available
     */
    public function hasInstallment(): bool
    {
        return !$this->hasSaleProductsInCart() || $this->sale->has_installment;
    }

    /**
     * Check if cash on delivery (COD) is available
     */
    public function hasCOD(): bool
    {
        return !$this->hasSaleProductsInCart() || $this->sale->has_cod;
    }

    /**
     * Check if there are sale products in the cart
     */
    private function hasSaleProductsInCart(): bool
    {
        if (is_null($this->hasSaleProductsInCart)) {
            throw new \Exception('First need apply sale for cart');
        }

        return $this->hasSaleProductsInCart;
    }

    /**
     * Apply to items in cart
     */
    public function applyToCart(Cart $cart): void
    {
        $productSaleList = [];
        $this->hasSaleProductsInCart = false;

        $products = $cart->availableItems()->map(fn ($item) => $item->product);

        if ($this->hasSale()) {
            $products = $this->sortCartProductsForSale($products);
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
            $sales = [];
            if (!$this->hasFakeSale()) {
                $sales[self::PRODUCT_DISCOUNT] = $this->getProductDiscountAsSale($item->product);
            }
            /** @var \App\Models\Data\SaleData|null $generalSale */
            $generalSale = $productSaleList[$item->product->id] ?? null;
            if ($generalSale) {
                $sales[self::GENERAL_SALE_KEY] = $generalSale;
            }
            $finalPrice = $generalSale ? $generalSale->price : $item->product->price;

            $this->applyUserSales($item->product, $sales, $finalPrice);
            $item->product->setSales($sales, $finalPrice);
        }
    }

    /**
     * Apply the sorting to a collection of products in cart.
     */
    private function sortCartProductsForSale(Collection $products): Collection
    {
        return match ($this->sale->algorithm) {
            SaleAlgorithm::ASCENDING => $products->sortBy('price'),
            SaleAlgorithm::DESCENDING => $products->sortByDesc('price'),
            default => $products,
        };
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

    /**
     * Apply a promocode to the user's cart.
     */
    public function applyPromocode(string $promocodeCode): void
    {
        if (!$promocode = $this->getPromocodeByCode($promocodeCode)) {
            return;
        }

        $user = auth()->user();
        if (!$user instanceof User) {
            if (Cookie::get(self::COOKIE_KEY_FOR_PENDING_PROMOCODE) != $promocode->code) {
                Cookie::queue(self::COOKIE_KEY_FOR_PENDING_PROMOCODE, $promocode->code, 60 * 24 * 7);
                View::share('pendingPromocode', $promocode);
            }

            return;
        }

        $this->applyPromocodeToUser($promocode, $user);
    }

    /**
     * Apply a pending promocode to the user's cart.
     */
    public function applyPendingPromocode($user): void
    {
        if (!$user instanceof User) {
            return;
        }

        $pendingPromocodeCode = Cookie::get(self::COOKIE_KEY_FOR_PENDING_PROMOCODE);
        if (!$pendingPromocodeCode) {
            return;
        }
        Cookie::queue(Cookie::forget(self::COOKIE_KEY_FOR_PENDING_PROMOCODE));

        if (!$promocode = $this->getPromocodeByCode($pendingPromocodeCode)) {
            return;
        }

        $this->applyPromocodeToUser($promocode, $user);
    }

    /**
     * Retrieve a promocode by its code.
     */
    private function getPromocodeByCode(string $code): ?Promocode
    {
        return Promocode::query()->firstWhere('code', $this->preparePromocodeCode($code));
    }

    /**
     * Prepare the promocode code.
     */
    private function preparePromocodeCode(string $promocodeCode): string
    {
        return trim($promocodeCode); // + additional logic
    }

    /**
     * Apply the promocode to the user.
     */
    private function applyPromocodeToUser(Promocode $promocode, User $user): void
    {
        if ($user->cart->promocode_id === $promocode->id) {
            return;
        }
        /** @var \App\Models\User\UserPromocode */
        $usedPromocode = $user->usedPromocodes()->firstOrCreate(
            ['promocode_id' => $promocode->id],
            ['apply_count' => 1],
        );
        if ($promocode->activations_count && $usedPromocode->apply_count > $promocode->activations_count) {
            return;
        }
        if (!$usedPromocode->wasRecentlyCreated) {
            $usedPromocode->apply_count++;
            $usedPromocode->canceled_at = null;
        }
        $usedPromocode->expired_at = $promocode->getExpiredDate();
        $usedPromocode->save();

        $user->cart->update(['promocode_id' => $promocode->id]);
        $user->cart->refresh();

        $this->setUp();
    }

    /**
     * Checks if there is a general sale with a fake algorithm.
     */
    private function hasFakeSale(): bool
    {
        return $this->sale?->algorithm->isFake() ?? false;
    }
}
