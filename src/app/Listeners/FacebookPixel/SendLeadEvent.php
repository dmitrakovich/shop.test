<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\SocialSubscription;

class SendLeadEvent extends AbstractFacebookPixelListener
{
    /**
     * Handle the event.
     */
    public function handle(SocialSubscription $socialSubscriptionEvent): void
    {
        $event = $this->generateEvent($socialSubscriptionEvent)
            ->setEventName('Lead');

        $this->sendEvent($event);
    }
}
