<?php

namespace App\Notifications;

use App\Models\Payments\Installment;

class InstallmentPaymentSms extends AbstractSmsTraffic
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(private readonly Installment $installment) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $nextPaymentDate = $this->installment->getNextPaymentDate()->format('d.m.Y');

        return <<<SMS
        {$this->installment->order->first_name}, благодарим за покупку!

        Напоминаем о следующем платеже по договору №{$this->installment->contract_number} на сумму {$this->installment->monthly_fee} BYN. Оплату необходимо произвести в срок до {$nextPaymentDate}.
        С заботой о Вас, компания BAROCCO!
        SMS;
    }
}
