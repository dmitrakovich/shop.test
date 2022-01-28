<?php

namespace App\Listeners;

use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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

        if (!empty($event->user)) {
            $device->user()->associate($event->user);
            $device->cart()->associate($event->user->cart);
        }

        if (!empty($event->order)) {
            //
        }

        $device->save();
    }
}
