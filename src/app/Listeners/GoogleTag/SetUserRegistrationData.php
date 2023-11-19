<?php

namespace App\Listeners\GoogleTag;

use App\Events\Analytics\Registered;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class SetUserRegistrationData extends AbstractGoogleTagListener
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        GoogleTagManagerFacade::flash([
            'event' => 'user_event',
            'event_id' => $event->eventId,
            'event_label' => 'userRegistration',
            'event_category' => 'user',
            'event_action' => 'userRegistration',
        ]);
    }
}
