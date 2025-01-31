<?php

namespace App\Http\Controllers\Api;

use App\Data\Cart\AddToCartData;
use App\Facades\Cart as CartFacade;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;

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
}
