<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Models\Orders\OrderItem;

class UpdateOrderItemsStatus
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->order->isCanceled()) {
            $event->order->items->each(fn (OrderItem $orderItem) => $orderItem->cancel());
        }
    }
}
