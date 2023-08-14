<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Facades\Sale;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\SyncRequest;
use App\Models\Cart;
use App\Models\User\User;
use App\Models\Data\SaleData;
use App\Models\Orders\Order;

class OrderService implements OrderServiceInterface
{
    public function store(StoreRequest $request, Cart $cart, User $user)
    {
        $orderData = $request->getValidatedData();
        $orderData->setUser($user);

        if (!$request instanceof SyncRequest) {
            Sale::applyToOrder($cart, $orderData);
            $orderData->total_price = $cart->getTotalPrice();
        }

        $order = Order::create($orderData->prepareToSave());

        if ($request instanceof SyncRequest) {
            foreach ($cart->items as $item) {
                $order->data()->create([
                    'product_id' => $item->product_id,
                    'size_id' => $item->size_id,
                    'count' => $item->count,
                    'buy_price' => 0,
                    'price' => $item->price,
                    'old_price' => $item->price,
                    'current_price' => $item->price,
                ]);
            }
            $order->adminComments()->create([
                'comment' => 'Заказ импортирован из modny.by. Старый номер ' . intval($request->input('id')),
            ]);
        } else {
            $adminComment = '';
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
                $sales = array_map(
                    fn (SaleData $saleData) => "- {$saleData->label} {$saleData->discount_percentage}%",
                    $item->product->getSales()
                );
                if (!empty($sales)) {
                    $adminComment .= (!empty($adminComment) ? PHP_EOL . PHP_EOL : '')
                        . $item->product->shortName() . PHP_EOL . implode(PHP_EOL, $sales);
                }
            }
            if (!empty($adminComment)) {
                $order->adminComments()->create(['comment' => $adminComment]);
            }
        }

        return $order;
    }
}
