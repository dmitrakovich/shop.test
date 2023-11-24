<?php

namespace App\Events\Analytics;

use App\Enums\Order\OrderMethods;
use App\Models\Data\UserData;
use FacebookAds\Object\ServerSide\ActionSource;

class OfflinePurchase extends Purchase
{
    /**
     * {@inheritdoc}
     */
    protected function setEventId(): void
    {
        $this->eventId = $this->order->id;
    }

    /**
     * {@inheritdoc}
     */
    protected function setSourceUrl(): void
    {
        $this->sourceUrl = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setActionSource(): void
    {
        $this->actionSource = match ($this->order->order_method) {
            OrderMethods::EMAIL => ActionSource::EMAIL,
            OrderMethods::PHONE => ActionSource::PHONE_CALL,
            OrderMethods::CHAT,
            OrderMethods::INSTAGRAM,
            OrderMethods::VIBER,
            OrderMethods::TELEGRAM,
            OrderMethods::WHATSAPP, => ActionSource::CHAT,
            OrderMethods::DEFAULT,
            OrderMethods::ONECLICK => ActionSource::WEBSITE,
            default => ActionSource::OTHER,
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function setVisitId(): void
    {
        $this->visitId = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUserData(): void
    {
        $this->userData = new UserData([
            'first_name' => $this->order->first_name,
            'last_name' => $this->order->last_name,
            'phone' => $this->order->phone,
            'country_code' => $this->order->country?->code,
        ]);
        $this->userData->setExternalIds(
            array_filter([$this->order->user_id])
        );
    }
}
