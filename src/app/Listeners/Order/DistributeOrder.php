<?php

namespace App\Listeners\Order;

use App\Events\OrderCreated;
use App\Services\Order\OrdersDistributionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DistributeOrder implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(private OrdersDistributionService $ordersDistributionService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $this->ordersDistributionService->distributeOrder($event->order);
    }
}
