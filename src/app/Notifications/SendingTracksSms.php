<?php

namespace App\Notifications;

use App\Models\Orders\Order;

class SendingTracksSms extends AbstractSmsTraffic
{
    /**
     * mailing ID
     */
    public ?int $mailingId = 3;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        private Order $order,
    ) {
    }

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $track = $this->order->track->track_number;
        $orderId = $this->order->id;
        $link = "https://belpost.by/Otsleditotpravleniye?number=$track";

        return "Ваш заказ №{$orderId} отправлен. Трек-номер {$track}. Отследите посылку {$link}";
    }
}
