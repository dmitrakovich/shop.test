<?php

namespace App\Listeners;

use App\Models\Orders\Order;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;

class SyncOrderHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private Order $order)
    {
    }

    /**
     * Handle the event.
     *
     * @param  Registered|Login  $event
     * @return void
     */
    public function handle(Registered|Login $event)
    {
        if (! empty($event->user->phone)) {
            $this->order
                ->where('phone', $event->user->phone)
                ->update(['user_id' => $event->user->id]);
        }
    }
}
