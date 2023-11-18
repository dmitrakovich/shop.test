<?php

namespace App\Services\Api\Facebook;

use FacebookAds\Api;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;

class ConversionsApiService
{
    public function __construct(protected Api $api, protected int $pixelId)
    {
    }

    public function test()
    {
        $user_data = (new UserData())
            ->setEmails(array('joe@eg.com'))
            ->setPhones(array('12345678901', '14251234567'))
            // It is recommended to send Client IP and User Agent for Conversions API Events.
            ->setClientIpAddress($_SERVER['REMOTE_ADDR'])
            ->setClientUserAgent($_SERVER['HTTP_USER_AGENT'])
            ->setFbc('fb.1.1554763741205.AbCdEfGhIjKlMnOpQrStUvWxYz1234567890')
            ->setFbp('fb.1.1558571054389.1098115397');

        $content = (new Content())
            ->setProductId('product123')
            ->setQuantity(1)
            ->setDeliveryCategory(DeliveryCategory::HOME_DELIVERY);

        $custom_data = (new CustomData())
            ->setContents(array($content))
            ->setCurrency('usd')
            ->setValue(123.45);

        $event = (new Event())
            ->setEventName('Purchase')
            ->setEventTime(time())
            ->setEventSourceUrl('http://jaspers-market.com/product/123')
            ->setUserData($user_data)
            ->setCustomData($custom_data)
            ->setActionSource(ActionSource::WEBSITE);

        $events = array();
        array_push($events, $event);

        $request = (new EventRequest($this->pixelId))
            ->setEvents($events);
        $response = $request->execute();

        dd($response);
    }
}
