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
    public function __construct(private readonly Order $order) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $track = $this->order->track->track_number;

        return <<<SMS
        {$this->order->first_name}, ваш заказ №{$this->order->id} передан в службу доставки Белпочты!

        Трек-номер {$track}.
        Отслеживайте отправление перейдя по ссылке: https://belpost.by/Otsleditotpravleniye?number={$track}.

        Благодарим за заказ!
        SMS;
    }
}
