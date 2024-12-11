<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Facades\Sale;
use App\Http\Requests\Order\StoreRequest;
use App\Models\Cart;
use App\Models\Data\SaleData;
use App\Models\Orders\Order;
use App\Models\User\User;

class OrderService implements OrderServiceInterface
{
    /**
     * @var int Maximum quantity of items per size
     */
    const MAX_PER_SIZE_LIMIT = 1;

    /**
     * {@inheritdoc}
     */
    public function store(StoreRequest $request, Cart $cart, User $user)
    {
        $orderData = $request->getValidatedData();
        $orderData->setUser($user);

        Sale::applyToOrder($cart, $orderData);
        $orderData->total_price = $cart->getTotalPrice();

        $order = Order::query()->create($orderData->prepareToSave());

        $adminComment = '';
        foreach ($cart->availableItems() as $item) {
            $order->data()->create([
                'product_id' => $item->product_id,
                'size_id' => $item->size_id,
                'count' => min($item->count, self::MAX_PER_SIZE_LIMIT),
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

        return $order;
    }
}
