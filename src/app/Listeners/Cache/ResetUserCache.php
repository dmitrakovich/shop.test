<?php

namespace App\Listeners\Cache;

use App\Models\User\User;
use Illuminate\Support\Facades\Cache;

class ResetUserCache
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
        $user = $event->user;

        if ($user instanceof User) {
            Cache::forget($user->getCacheKey());
        }
    }
}
