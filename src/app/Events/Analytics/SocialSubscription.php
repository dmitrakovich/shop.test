<?php

namespace App\Events\Analytics;

class SocialSubscription extends AbstractAnalyticEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(public string $eventId)
    {
        $this->setAnalyticData();
    }
}
