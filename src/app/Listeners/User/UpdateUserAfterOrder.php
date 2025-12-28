<?php

namespace App\Listeners\User;

use App\Enums\User\OrderType;
use App\Events\Order\OrderCreated;
use App\Models\Orders\Order as OnlineOrder;
use App\Models\User\User;

class UpdateUserAfterOrder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        if (!($user = $event->user) instanceof User) {
            return;
        }

        if ($user->group_id === 1) {
            $user->update(['group_id' => 2]);
        }

        if ($event->order instanceof OnlineOrder) {
            $user->metadata()->updateOrCreate([], [
                'last_order_type' => OrderType::ONLINE,
                'last_order_date' => $event->order->created_at,
            ]);
        }
    }
}
