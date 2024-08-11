<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\AbstractAnalyticEvent;
use App\Services\Api\Facebook\ConversionsApiService;
use FacebookAds\Object\ServerSide\Event;

/**
 * Class AbstractFacebookPixelListener
 *
 * This abstract class provides a foundation for Facebook Pixel listeners with common functionality,
 * such as creating the event listener and generating Facebook Pixel events.
 */
abstract class AbstractFacebookPixelListener
{
    /**
     * Generate a Facebook Pixel event based on the provided analytic event.
     */
    protected function generateEvent(AbstractAnalyticEvent $event): Event
    {
        return (new Event)
            ->setEventId($event->eventId)
            ->setEventTime($event->eventTime)
            ->setEventSourceUrl($event->sourceUrl)
            ->setUserData($event->userData)
            ->setActionSource($event->actionSource);
    }

    /**
     * Send the generated Facebook Pixel event using the Conversions API service.
     */
    protected function sendEvent(Event $event): void
    {
        app(ConversionsApiService::class)->sendEvent($event);
    }

    /**
     * Send the generated Facebook Pixel events using the Conversions API service.
     */
    protected function sendEvents(array $events): void
    {
        app(ConversionsApiService::class)->sendEvents($events);
    }
}
