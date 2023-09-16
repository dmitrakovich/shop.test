<?php

namespace App\Events;

use App\Models\Orders\Order;
use App\Models\User\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order,
        public ?User $user = null,
        public bool $shouldUpdateInventory = true
    ) {
    }
}
