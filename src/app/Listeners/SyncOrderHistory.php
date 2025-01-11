<?php

namespace App\Listeners;

use App\Events\User\UserLogin;
use App\Models\Orders\Order;
use Illuminate\Auth\Events\Registered;

class SyncOrderHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private Order $order) {}

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Registered|UserLogin $event)
    {
        if (!empty($event->user->phone)) {
            $this->order
                ->where('phone', $event->user->phone)
                ->update(['user_id' => $event->user->id]);
        }
    }
}
