<?php

namespace App\Notifications;

class DiscountAfterRegisterSms extends AbstractSmsTraffic
{
    /**
     * mailing ID
     */
    public ?int $mailingId = 1;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private string $discount) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        return "Ваш бонус за регистрацию {$this->discount}% закончится через 3 дня. Успейте сделать заказ! https://barocco.by/lnk/k7eTO4U";
    }
}
