<?php

namespace App\Listeners;

use App\Events\User\UserLogin;
use App\Facades\Device;
use App\Models\Favorite;

class MergeFavorites
{
    /**
     * Handle the event.
     */
    public function handle(UserLogin $event): void
    {
        Favorite::query()
            ->where('device_id', Device::id())
            ->update(['user_id' => $event->user->id]);
    }
}
