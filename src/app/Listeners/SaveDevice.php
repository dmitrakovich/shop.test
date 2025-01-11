<?php

namespace App\Listeners;

use App\Facades\Device;
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
     */
    public function handle($event): void
    {
        $device = Device::current();
        $user = $event->user ?? Auth::user();

        if (!empty($user)) {
            $device->user()->associate($user);
        }

        if (!empty($event->order)) {
            $event->order->device()->associate($device);
            $event->order->save();
        }

        $device->save();
    }
}
