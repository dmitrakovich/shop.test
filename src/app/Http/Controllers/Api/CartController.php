<?php

namespace App\Http\Controllers\Api;

use App\Data\Cart\AddToCartData;
use App\Facades\Cart as CartFacade;
use App\Facades\Sale;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Cart\DeliveryMethodResource;
use App\Http\Resources\Cart\PaymentMethodResource;
use Deliveries\DeliveryMethod;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Payments\PaymentMethod;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

class CartController extends Controller
{
    /**
     * Add one or more items to the shopping cart.
     */
    public function addToCart(AddToCartData $data): CartResource
    {
        $cart = CartFacade::getCart();
        foreach ($data->sizes as $size) {
            $cart->addItem($data->product->id, $size->id);
        }

        return new CartResource($cart);
    }

    /**
     * Display the specified resource.
     */
    public function show(): CartResource
    {
        return new CartResource(CartFacade::getCart());
    }

    /**
     * Remove a specific item from the cart.
     */
    public function removeItem(int $itemId): CartResource
    {
        return new CartResource(
            CartFacade::getCart()->removeItemById($itemId)
        );
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        CartFacade::getCart()->clear();
    }

    /**
     * Get available delivery methods for the current cart and location.
     */
    public function getDeliveries(): AnonymousResourceCollection
    {
        Sale::applyToCart(CartFacade::getCart());

        $deliveryMethods = DeliveryMethod::active()
            ->filterFitting(Sale::hasFitting())
            ->filterByCountry(SxGeo::getCountry())
            ->get();

        return DeliveryMethodResource::collection($deliveryMethods);
    }

    /**
     * Get available payment methods for the current cart and location.
     */
    public function getPayments(): AnonymousResourceCollection
    {
        $cart = CartFacade::getCart();
        Sale::applyToCart($cart);

        $paymentsMethods = PaymentMethod::active()
            ->filterInstallment($cart->availableInstallment() && Sale::hasInstallment())
            ->filterCOD(Sale::hasCOD())
            ->filterByCountry(SxGeo::getCountry())
            ->get();

        return PaymentMethodResource::collection($paymentsMethods);
    }
}
