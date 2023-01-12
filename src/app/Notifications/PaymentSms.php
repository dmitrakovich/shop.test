<?php

namespace App\Notifications;

use App\Models\Orders\Order;

class PaymentSms extends AbstractSmsTraffic
{
  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(
    private Order $order,
    private string $paymentNum
  ) {
  }

  /**
   * Content for sms message
   */
  public function getContent(): string
  {
    return ($this->order->first_name ? ($this->order->first_name . ', ') : '') . 'Вам выставлен счет № ' . $this->paymentNum . ' - подробнее по ссылке ' . route('pay.erip', $this->paymentNum, true);
  }
}
