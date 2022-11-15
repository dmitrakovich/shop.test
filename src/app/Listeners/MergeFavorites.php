<?php

namespace App\Listeners;

use App\Models\Device;
use App\Models\Favorite;

class MergeFavorites
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
    public function handle($event)
    {
        Favorite::withoutGlobalScope('for_user')
            ->where('device_id', Device::getId())
            ->update(['user_id' => $event->user->id]);
    }
}
