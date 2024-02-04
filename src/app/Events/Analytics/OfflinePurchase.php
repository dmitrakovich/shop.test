<?php

namespace App\Events\Analytics;

use App\Enums\Order\OrderMethod;
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
            OrderMethod::EMAIL => ActionSource::EMAIL,
            OrderMethod::PHONE => ActionSource::PHONE_CALL,
            OrderMethod::CHAT,
            OrderMethod::INSTAGRAM,
            OrderMethod::VIBER,
            OrderMethod::TELEGRAM,
            OrderMethod::WHATSAPP, => ActionSource::CHAT,
            OrderMethod::DEFAULT,
            OrderMethod::ONECLICK => ActionSource::WEBSITE,
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
