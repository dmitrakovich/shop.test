<?php

namespace App\Contracts;

use App\Models\Orders\Order;

interface PaymentMethodContract
{
  /**
   * Create new payment
   * @return array
   */
  public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []);

}
