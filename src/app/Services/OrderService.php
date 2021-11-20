<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Contracts\OrderServiceIntarface;
use App\Http\Requests\Order\StoreRequest;

class OrderService implements OrderServiceIntarface
{
    public function store(StoreRequest $request, Cart $cart)
    {
        $userData = $request->validated();
        $userData['total_price'] = $cart->getTotalPrice();
        // dd($userData);

        $order = Order::create($userData);

        foreach ($cart->items as $item) {
            $order->data()->create([
                'product_id' => $item->product_id,
                'size_id' => $item->size_id,
                'count' => $item->count,
                'buy_price' => $item->product->buy_price,
                'price' => $item->product->price,
                'old_price' => $item->product->getOldPrice(),
                'current_price' => $item->product->getPrice(),
                'discount' => $item->product->getSalePercentage(),
            ]);
        }

        return $order;
    }
}
