<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Facades\Currency;
use Illuminate\Support\Facades\Auth;
use App\Contracts\OrderServiceIntarface;
use Illuminate\Foundation\Http\FormRequest;

class OrderService implements OrderServiceIntarface
{
    public function store(FormRequest $request, Cart $cart)
    {
        $userData = $request->validated();

        // dd($userData);

        $order = Order::create([
            'user_name' => $userData['name'],
            'user_id' => Auth::check() ? Auth::id() : null,
            'type' => 'retail',
            'email' => $userData['email'] ?? null,
            'phone' => $userData['phone'],
            // 'comment' => $request->input('comment'),
            'currency' => Currency::getCurrentCurrency()->code,
            'rate' => Currency::getCurrentCurrency()->rate,
            // 'promocode_id' => Cart::getPromocodeId(),
            // 'country' => $address['country'] ?? null,
            // 'region' => $address['administrative_area_level_1'] ?? null,
            'city' => $userData['city'] ?? null,
            // 'zip' => $address['postal_code'] ?? null,
            // 'street' => $address['route'] ?? null,
            // 'house' => $address['street_number'] ?? null,
            'user_addr' => $userData['user_addr'],
            'payment' => $userData['payment_name'],
            'payment_code' => $userData['payment_code'],
            // 'payment_cost' => isset($payment['price']) ? $payment['price'] : 0.00,
            'delivery' => $userData['delivery_name'],
            'delivery_code' => $userData['delivery_code'],
            // 'delivery_cost' => $delivery['price'] ?? null,
            // 'delivery_point' => $point['address'] ?? '',
            // 'delivery_point_code' => $point['code'] ?? '',
            // 'source' => Cookie::has('soc_order') ? 1 : 0
        ]);

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
