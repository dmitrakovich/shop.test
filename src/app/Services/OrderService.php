<?php

namespace App\Services;

use App\Data\Order\OrderData;
use App\Enums\User\BanReason;
use App\Events\OrderCreated;
use App\Facades\Sale;
use App\Models\Cart;
use App\Models\Data\SaleData;
use App\Models\Orders\Order;

class OrderService
{
    /**
     * @var int Maximum quantity of items per size
     */
    const MAX_PER_SIZE_LIMIT = 1;

    public function __construct(private readonly UserService $userService) {}

    /**
     * Store order (create new)
     */
    public function store(Cart $cart, OrderData $orderData): Order
    {
        abort_if(!$cart->hasAvailableItems(), 404, 'Товаров нет в наличии');

        if ($cart->isSuspicious()) {
            $cart->device->ban(BanReason::SUSPICIOUS_ORDER);
        }

        Sale::applyToOrder($cart, $orderData);

        $user = $this->userService->getOrCreateByOrderData($orderData);
        $order = Order::query()->create([
            ...$orderData->prepareToSave(),
            'total_price' => $cart->getTotalPrice(),
            'user_id' => $user->id,
        ]);

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
