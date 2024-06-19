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
    public function __construct(
        private Installment $installment,
    ) {}

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $order = $this->installment->order;
        $nextPaymentDate = $this->installment->getNextPaymentDate()->format('d.m.Y');

        return "{$order->first_name}, минимальный платеж по кредитному договору {$this->installment->contract_number} суммой {$this->installment->monthly_fee} руб. Оплата до {$nextPaymentDate}. Благодарим, если уже совершили платёж";
    }
}
