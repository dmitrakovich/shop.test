<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\SocialSubscription;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLeadEvent extends AbstractFacebookPixelListener implements ShouldQueue
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
