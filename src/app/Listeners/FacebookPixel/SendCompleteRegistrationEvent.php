<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\Registered;

class SendCompleteRegistrationEvent extends AbstractFacebookPixelListener
{
    /**
     * Handle the event.
     */
    public function handle(Registered $registeredEvent): void
    {
        $event = $this->generateEvent($registeredEvent)
            ->setEventName('CompleteRegistration');

        $this->sendEvent($event);
    }
}
