<?php

namespace App\Listeners;

use App\Enums\Order\OrderItemStatus;
use App\Events\Order\OrderStatusChanged;

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
            $event->order->items()->update(['status' => OrderItemStatus::CANCELED]);
        }
    }
}
