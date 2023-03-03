<?php

namespace App\Listeners\User;

use App\Models\User\User;

class UpdateUserGroup
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
        /** @var User $user */
        if (!($user = $event->user) instanceof User) {
            return;
        }

        if ($user->group_id === 1) {
            $user->update(['group_id' => 2]);
        }
    }
}
