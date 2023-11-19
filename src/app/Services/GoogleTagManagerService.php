<?php

namespace App\Services;

use App\Events\Analytics\ProductView;
use App\Events\Analytics\Purchase;
use App\Facades\Currency;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Data\UserData;
use App\Models\Orders\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Spatie\GoogleTagManager\DataLayer;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class GoogleTagManagerService
{
    /**
     * Set GTM view event for product page
     */
    public function setViewForProduct(ProductView $event): void
    {
        $this->pushViewEvent('product', $event->eventId, $event->userData, ['id' => $event->product->id]);
    }

    /**
     * Set GTM view event for cart page
     */
    public function setViewForCart(Cart $cart): void
    {
        GoogleTagManagerFacade::view('cart', [
            'ids' => $cart->items->implode('product_id', ','),
            'value' => $cart->getTotalPrice('USD'),
        ]);
    }

    /**
     * Set GTM view event for order complete page
     */
    public function setViewForOrder(): void
    {
        GoogleTagManagerFacade::view('order_complete');
    }

    /**
     * Set GTM view event for index page
     */
    public function setViewForIndex(): void
    {
        GoogleTagManagerFacade::view('index');
    }

    /**
     * Set GTM view event for other pages
     */
    public function setViewForOther(): void
    {
        GoogleTagManagerFacade::view('other');
    }

    /**
     * Set GTM view event for catalog page
     *
     * @param  Collection  $products
     */
    public function setViewForCatalog($products, string|Category $category, ?string $searchQuery = null): void
    {
        if ($category instanceof Category) {
            $category = $category->getNameWithParents();
        }

        if (!empty($searchQuery)) {
            GoogleTagManagerFacade::view('search_result', [
                'query' => $searchQuery,
                'ids' => $products->implode('id', ','),
            ]);
        } else {
            GoogleTagManagerFacade::view('catalog', [
                'category' => $category,
                'ids' => $products->implode('id', ','),
            ]);
        }
    }

    /**
     * Prepare products array
     */
    public static function prepareProduct(Product $product, ?int $quantity = null): DataLayer
    {
        return new DataLayer(array_filter([
            'name' => $product->brand->name . ' ' . $product->id,
            'id' => $product->id,
            'price' => $product->getPrice('USD'),
            'brand' => $product->brand->name,
            'category' => $product->category->getNameWithParents(),
            'quantity' => $quantity,
        ]));
    }

    /**
     * Prepare products array
     *
     * @param  Collection  $products
     */
    public function prepareProductsArray($products): array
    {
        return $products->map(
            fn (Product $product) => self::prepareProduct($product)->toArray()
        )->toArray();
    }

    /**
     * Set GTM ecommerce event for catalog page
     *
     * @param  Collection  $products
     */
    public function setEcommerceForCatalog($products): void
    {
        self::setEcommerceImpressions($this->prepareProductsArray($products));
    }

    /**
     * Set events for catalog page
     *
     * @param  Collection  $products
     */
    public function setForCatalog($products, string|Category $category, ?string $searchQuery = null): void
    {
        $this->setViewForCatalog($products, $category, $searchQuery);
        $this->setEcommerceForCatalog($products);
    }

    /**
     * Generate & return dataLayer script for catalog page
     *
     * @param  Collection  $products
     */
    public function getForCatalogArrays($products, string|Category $category, ?string $searchQuery = null): array
    {
        $this->setForCatalog($products, $category, $searchQuery);

        return GoogleTagManagerFacade::getPushData()->map(fn (DataLayer $dataLayer) => $dataLayer->toArray())->toArray();
    }

    /**
     * Set GTM ecommerce product impressions event
     */
    public static function setEcommerceImpressions(array $impressions): void
    {
        GoogleTagManagerFacade::ecommerce('productImpressions', [
            'impressions' => $impressions,
        ]);
    }

    /**
     * Set GTM ecommerce remove from cart flash event
     */
    public function setProductRemoveFlashEvent(Product $product, int $quantity): void
    {
        GoogleTagManagerFacade::ecommerceFlash('productRemove', [
            'remove' => [
                'products' => [
                    self::prepareProduct($product, $quantity)->toArray(),
                ],
            ],
        ]);
    }

    /**
     * Set GTM ecommerce purchase event
     */
    public function setPurchaseEvent(Purchase $event): void
    {
        $order = $event->order;
        $gtmProducts = $order->items->map(
            fn (OrderItem $item) => self::prepareProduct($item->product, $item->count)->toArray()
        )->toArray();

        if ($order->isOneClick()) {
            $item = $order->items->first();
            $this->pushProductAddEvent($event->eventId, $item->product, $item->count);
        }

        $this->pushProductPurchaseEvent($event->eventId, $order->id, $gtmProducts);

        if ($order->isOneClick()) {
            GoogleTagManagerFacade::ecommerce('productOneClickOrder', []);
        }
    }

    /**
     * Push GTM view_page event
     */
    private function pushViewEvent(string $page, string $eventId, UserData $userData, ?array $content = null): void
    {
        $gtmUserData = $userData->normalizeForGtm();
        $currency = Currency::getCurrentCurrency();

        GoogleTagManagerFacade::push(array_filter([
            'pageType' => $page,
            'user_type' => $gtmUserData['user_type'] ?? null,
            'user_id' => $gtmUserData['user_id'] ?? null,
            'user_data' => $gtmUserData,
            'site_price' => [
                'name' => $currency->code,
                'rate' => $currency->rate,
            ],
            'page_content' => $content,
            'event' => 'view_page',
            'event_id' => $eventId,
        ]));
    }

    /**
     * Set GTM ecommerce add to cart event
     */
    private function pushProductAddEvent(string $eventId, Product $product, int $quantity): void
    {
        GoogleTagManagerFacade::push([
            'ecommerce' => [
                'currencyCode' => 'USD',
                'add' => [
                    'products' => [
                        self::prepareProduct($product, $quantity)->toArray(),
                    ],
                ],
            ],
            'event' => 'ecom_event',
            'event_id' => $eventId,
            'event_label' => 'productAdd',
            'event_category' => 'ecommerce',
            'event_action' => 'productAdd',
        ]);
    }

    /**
     * Push GTM ProductPurchase ecommerce event
     */
    private function pushProductPurchaseEvent(string $eventId, int $orderId, array $products): void
    {
        GoogleTagManagerFacade::push([
            'ecommerce' => [
                'currencyCode' => 'USD',
                'purchase' => [
                    'actionField' => [
                        'id' => $orderId,
                        'goal_id' => 230549930,
                    ],
                    'products' => $products,
                ],
            ],
            'event' => 'ecom_event',
            'event_id' => $eventId,
            'event_label' => 'productPurchase',
            'event_category' => 'ecommerce',
            'event_action' => 'productPurchase',
        ]);
    }
}
