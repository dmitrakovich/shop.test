<?php

namespace App\Observers;

use App\Models\Logs\OrderItemPickupStatusLog;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderItemExtended;
use App\Services\LogService;
use App\Services\Order\OrderItemInventoryService;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        if ($orderItem instanceof OrderItemExtended) {
            if ($orderItem->order->created_at->addSeconds(5)->isPast()) {
                (new LogService)->logOrderAction($orderItem->order_id, "Товар {$orderItem->product_id} добавлен к заказу");
            }
        }
    }

    /**
     * Handle the OrderItem "saving" event.
     */
    public function saving(OrderItem $orderItem): void
    {
        if ($orderItem->isDirty('status_key')) {
            $orderItem->status_updated_at = now();

            if ($orderItem->status_key !== 'new') {
                (new LogService)->logOrderAction($orderItem->order_id, "Товару {$orderItem->product_id} присвоен статус “{$orderItem->status_key}”");
            }
        }
    }

    /**
     * Handle the OrderItem "saved" event.
     */
    public function saved(OrderItem $orderItem): void
    {
        if ($orderItem->isDirty('status_key')) {
            (new OrderItemInventoryService)->handleChangeItemStatus($orderItem->refresh());

            if ($orderItem->status_key === 'pickup') {
                OrderItemPickupStatusLog::query()->firstOrCreate([
                    'order_item_id' => $orderItem->id,
                ]);
            }
        }
    }

    /**
     * Handle the OrderItem "deleting" event.
     */
    public function deleting(OrderItem $orderItem): void
    {
        (new LogService)->logOrderAction($orderItem->order_id, "Товар {$orderItem->product_id} удален из заказа");
    }
}
