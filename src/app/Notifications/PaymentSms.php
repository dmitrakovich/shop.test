<?php

namespace App\Notifications;

class PaymentSms extends AbstractSmsTraffic
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $paymentNum,
        private readonly string $link,
        private readonly ?string $firstName = null
    ) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        return ($this->firstName ? ($this->firstName . ', ') : '') . 'Вам выставлен счет № ' . $this->paymentNum . ' - подробнее по ссылке ' . $this->link;
    }
}
