<?php

namespace App\Listeners\Order;

use App\Events\OrderCreated;
use App\Services\Order\InstallmentOrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInstallment
{
    /**
     * Create the event listener.
     */
    public function __construct(private InstallmentOrderService $installmentOrderService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        if ($event->order->hasInstallment()) {
            $this->installmentOrderService->createInstallmentForOrder($event->order);
        }
    }
}
