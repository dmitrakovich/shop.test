<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCompleteRegistrationEvent extends AbstractFacebookPixelListener implements ShouldQueue
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
