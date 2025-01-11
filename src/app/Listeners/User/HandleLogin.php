<?php

namespace App\Listeners\User;

use App\Events\User\UserLogin;
use App\Models\User\User;
use Illuminate\Auth\Events\Login;

class HandleLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            event(new UserLogin($event->guard, $event->user, $event->remember));
        }
    }
}
