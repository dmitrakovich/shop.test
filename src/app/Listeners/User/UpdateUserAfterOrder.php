<?php

namespace App\Listeners\User;

use App\Events\OrderCreated;
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
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        /** @var User $user */
        if (!($user = $event->user) instanceof User) {
            return;
        }

        if ($user->group_id === 1) {
            $user->update(['group_id' => 2]);
        }

        if ($event->order instanceof OnlineOrder) {
            $user->update(['has_online_orders' => true]);
        }
    }
}
