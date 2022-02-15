<?php

namespace App\Services;

use App\Models\Orders\OrderItem;
use Illuminate\Support\Collection;
use Spatie\GoogleTagManager\DataLayer;
use App\Models\{Cart, Category, Product};
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class GoogleTagManagerService
{
    /**
     * Set GTM view event for product page
     *
     * @param Product $product
     * @return void
     */
    public function setViewForProduct(Product $product): void
    {
        GoogleTagManagerFacade::view('product', ['id' => $product->id]);
    }

    /**
     * Set GTM view event for cart page
     *
     * @param Cart $cart
     * @return void
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
     *
     * @return void
     */
    public function setViewForOrder(): void
    {
        GoogleTagManagerFacade::view('order_complete');
    }

    /**
     * Set GTM view event for index page
     *
     * @return void
     */
    public function setViewForIndex(): void
    {
        GoogleTagManagerFacade::view('index');
    }

    /**
     * Set GTM view event for other pages
     *
     * @return void
     */
    public function setViewForOther(): void
    {
        GoogleTagManagerFacade::view('other');
    }

    /**
     * Set GTM view event for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return void
     */
    public function setViewForCatalog($products, $category, ?string $searchQuery = null): void
    {
        if ($category instanceof Category) {
            $category = $category->getNameWithParents();
        }

        if (!empty($searchQuery)) {
            GoogleTagManagerFacade::view('search_result', [
                'query' => $searchQuery,
                'ids' => $products->implode('id', ',')
            ]);
        } else {
            GoogleTagManagerFacade::view('catalog', [
                'category' => $category,
                'ids' => $products->implode('id', ',')
            ]);
        }
    }

    /**
     * Prepare products array
     *
     * @param Product $product
     * @param integer|null $quantity
     * @return DataLayer
     */
    public static function prepareProduct(Product $product, ?int $quantity = null): DataLayer
    {
        return new DataLayer(array_filter([
            'name' => $product->brand->name . ' '. $product->id,
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
     * @param Collection $products
     * @return array
     */
    public function prepareProductsArray($products): array
    {
        return $products->map(function (Product $product) {
            return self::prepareProduct($product)->toArray();
        })->toArray();
    }

    /**
     * Set GTM ecommerce event for catalog page
     *
     * @param Collection $products
     * @return void
     */
    public function setEcommerceForCatalog($products): void
    {
        self::setEcommerceImpressions($this->prepareProductsArray($products));
    }

    /**
     * Set events for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return void
     */
    public function setForCatalog($products, $category, ?string $searchQuery = null): void
    {
        $this->setViewForCatalog($products, $category, $searchQuery);
        $this->setEcommerceForCatalog($products);
    }

    /**
     * Generate & return dataLayer script for catalog page
     *
     * @param Collection $products
     * @param string|Category $category
     * @param string|null $searchQuery
     * @return array
     */
    public function getForCatalogArrays($products, $category, ?string $searchQuery = null): array
    {
        $this->setForCatalog($products, $category, $searchQuery);

        return GoogleTagManagerFacade::getPushData()->map(function (DataLayer $dataLayer) {
            return $dataLayer->toArray();
        })->toArray();
    }

    /**
     * Set GTM ecommerce product impressions event
     *
     * @param array $impressions
     * @return void
     */
    public static function setEcommerceImpressions(array $impressions): void
    {
        GoogleTagManagerFacade::ecommerce('productImpressions', [
            'impressions' => $impressions,
        ]);
    }

    /**
     * Set GTM ecommerce add to cart flash event
     *
     * @param Product $product
     * @param integer $quantity
     * @return void
     */
    public function setAddToCartFlashEvent(Product $product, int $quantity): void
    {
        GoogleTagManagerFacade::ecommerceFlash('productAdd', [
            'addToCart' => [
                'products' => [
                    self::prepareProduct($product, $quantity)->toArray()
                ]
            ],
        ]);
    }

    /**
     * Set GTM ecommerce remove from cart flash event
     *
     * @param Product $product
     * @param integer $quantity
     * @return void
     */
    public function setRemoveFromCartFlashEvent(Product $product, int $quantity): void
    {
        GoogleTagManagerFacade::ecommerceFlash('productRemove', [
            'removeFromCart' => [
                'products' => [
                    self::prepareProduct($product, $quantity)->toArray()
                ]
            ],
        ]);
    }

    /**
     * Set GTM ecommerce purchase event
     *
     * @param Collection $orderItems
     * @param integer $orderId
     * @param boolean $isOneClick
     * @return void
     */
    public function setPurchaseEvent($orderItems, int $orderId, bool $isOneClick): void
    {
        $gtmProducts = $orderItems->map(function (OrderItem $item) {
            return self::prepareProduct($item->product, $item->count)->toArray();
        })->toArray();

        if ($isOneClick) {
            $item = $orderItems->first();
            $this->setAddToCartFlashEvent($item->product, $item->count);
        }

        GoogleTagManagerFacade::ecommerce('productPurchase', [
            'purchase' => [
                'actionField' => ['id' => $orderId],
                'products' => $gtmProducts
            ],
        ]);

        if ($isOneClick) {
            GoogleTagManagerFacade::ecommerce('productOneClickOrder', []);
        }
    }
}
