<?php

namespace App\Facades;

use App\Data\Order\OneClickOrderData;
use App\Models\Cart as CartModel;
use App\Models\CartData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static CartModel getCart() Get cart model with data
 * @method static void addItem() Add item to cart
 * @method static void clear($onlyAvailable = false) Clear items from the shopping cart.
 * @method static void clearPromocode() Clear the applied promocode from the cart.
 *
 * @mixin CartModel
 */
class Cart extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }

    public static function makeTempCart(OneClickOrderData $oneClickOrderData): CartModel
    {
        $cart = new CartModel([]);
        $items = new Collection([]);

        foreach ($oneClickOrderData->sizes as $size) {
            $items->push(new CartData([
                'product_id' => $oneClickOrderData->product->id,
                'size_id' => $size->id,
                'count' => 1,
            ]));
        }
        $cart->setRelation('items', $items);

        return $cart;
    }
}
