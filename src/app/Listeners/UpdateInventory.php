<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\Order\OrderItemInventoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateInventory implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(private OrderItemInventoryService $orderItemInventoryService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        if ($event->shouldUpdateInventory) {
            $this->orderItemInventoryService->updateInventory($event->order->items);
        }
    }
}
