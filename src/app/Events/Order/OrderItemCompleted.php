<?php

namespace App\Events\Order;

use App\Models\Orders\OrderItem;
use Illuminate\Queue\SerializesModels;

class OrderItemCompleted
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly OrderItem $orderItem) {}
}
