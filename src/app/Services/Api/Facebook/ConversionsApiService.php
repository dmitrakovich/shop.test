<?php

namespace App\Services\Api\Facebook;

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\EventRequestAsync;
use FacebookAds\Object\ServerSide\EventResponse;
use FacebookAds\Object\ServerSide\UserData;
use GuzzleHttp\Promise\PromiseInterface;

class ConversionsApiService
{
    public function __construct(private Api $api, private int $pixelId)
    {
    }

    public function sendEvent(Event $event): EventResponse // PromiseInterface
    {
        return $this->sendEvents([$event]);
    }

    public function sendEvents(array $events): EventResponse // PromiseInterface
    {
        return (new EventRequest($this->pixelId)) // EventRequestAsync
            ->setEvents($events)
            // ->setTestEventCode('TEST39695') // !!!
            ->execute();
    }
}
