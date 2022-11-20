<?php

namespace App\Listeners;

use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class SaveDevice
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
    public function handle($event): void
    {
        $device = Device::getOrNew();
        $user = $event->user ?? Auth::user();

        if (!empty($user)) {
            $device->user()->associate($user);
            $device->cart()->associate($user->cart);
        }

        if (!empty($event->order)) {
            $event->order->device()->associate($device);
            $event->order->save();
        }

        $device->save();
    }
}
