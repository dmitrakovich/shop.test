<?php

namespace App\Listeners;

use App\Facades\Device;
use App\Models\Cart;
use App\Models\CartData;
use Illuminate\Auth\Events\Login;

class MergeCart
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
    public function handle(Login $event)
    {
        $deviceCart = Cart::query()->with('items')->firstWhere('device_id', Device::id());
        if (!$deviceCart) {
            return;
        }

        $userCart = Cart::query()->firstWhere('user_id', $event->user->id);
        if (!$userCart) {
            $deviceCart->update(['user_id' => $event->user->id]);

            return;
        }

        $deviceCart->items->each(function (CartData $cartItem) use ($userCart) {
            $userCart->addItem($cartItem->product_id, $cartItem->size_id);
        });
        $deviceCart->delete();
    }
}
