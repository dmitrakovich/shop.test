<?php

namespace App\Notifications;

use App\Models\Payments\Installment;
use App\Models\Payments\OnlinePayment;

class InstallmentPaymentSms extends AbstractSmsTraffic
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(
        private Installment $installment,
        private OnlinePayment $onlinePayment
    ) {
    }

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $order = $this->installment->order;
        $nextPaymentDate = $this->installment->getNextPaymentDate()->format('d.m.Y');

        return "{$order->first_name}, минимальный платеж по кредитному договору {$this->installment->contract_number} суммой {$this->installment->monthly_fee} руб. Оплата до {$nextPaymentDate} по счёту {$this->onlinePayment->link}. Благодарим, если уже совершили платёж";
    }
}
