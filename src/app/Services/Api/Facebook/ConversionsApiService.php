<?php

namespace App\Services\Api\Facebook;

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\EventRequestAsync;
use FacebookAds\Object\ServerSide\EventResponse;
use GuzzleHttp\Promise\PromiseInterface;

class ConversionsApiService
{
    public function __construct(private Api $api, private int $pixelId) {}

    public function sendEvent(Event $event): PromiseInterface
    {
        return $this->sendEvents([$event]);
    }

    public function sendEvents(array $events): PromiseInterface // EventResponse
    {
        return (new EventRequestAsync($this->pixelId)) // EventRequest
            ->setEvents($events)
            ->execute();
    }
}
