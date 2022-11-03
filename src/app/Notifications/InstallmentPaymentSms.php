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
    public function __construct(private Installment $installment)
    {
    }

    /**
     * Content for sms message
     */
    public function getContent(): string
    {
        $order = $this->installment->order;
        $nextPaymentDate =  $this->installment->getNextPaymentDate()->format('d.m.Y');

        return "{$order->first_name}, внесите платеж по рассрочке barocco.by за заказ {$order->id} до {$nextPaymentDate}. Сумма {$this->installment->monthly_fee}";
    }
}
