<?php

namespace App\Services;

use App\Data\Order\OrderData;
use App\Events\OrderCreated;
use App\Facades\Sale;
use App\Http\Requests\Order\StoreRequest;
use App\Models\Cart;
use App\Models\Data\SaleData;
use App\Models\Orders\Order;
use App\Models\User\User;

class OrderService
{
    /**
     * @var int Maximum quantity of items per size
     */
    const MAX_PER_SIZE_LIMIT = 1;

    /**
     * Store order (create new)
     */
    public function store(StoreRequest $request, Cart $cart, OrderData $orderData/*,  User $user*/): Order
    {
        // $orderData = $request->getValidatedData();
        // $orderData->setUser($user);
        // public function getValidatedData(): OrderData
        // {
        //     return new OrderData(...$this->validated());
        // }
        // public function setUser(User $user): self
        // {
        //     $this->user = $user;
        //     $this->user_id = $user->id;

        //     return $this;
        // }

        // public function prepareToSave(): array
        // {
        //     return array_filter((array)$this);
        // }

        // dd($orderData);

        Sale::applyToOrder($cart, $orderData);
        $orderData->total_price = $cart->getTotalPrice();

        $order = Order::query()->create($orderData->prepareToSave());

        $adminComments = [];
        foreach ($cart->availableItems() as $item) {
            $order->items()->create([
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
                $adminComments[] = $item->product->shortName() . PHP_EOL . implode(PHP_EOL, $sales);
            }
        }
        if (!empty($adminComments)) {
            $order->adminComments()->create([
                'comment' => implode(PHP_EOL . PHP_EOL, $adminComments),
            ]);
        }

        $cart->clear(onlyAvailable: true);
        $cart->clearPromocode();

        event(new OrderCreated($order, $user));

        return $order;
    }
}
