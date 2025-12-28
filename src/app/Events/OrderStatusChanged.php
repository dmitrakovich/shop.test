<?php

namespace App\Events;

use App\Enums\Order\OrderStatus;
use App\Models\Orders\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $order, public ?OrderStatus $prevStatus = null) {}
}
