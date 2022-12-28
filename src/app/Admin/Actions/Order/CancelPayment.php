<?php

namespace App\Admin\Actions\Order;

use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Payments\OnlinePayment;
use App\Services\Payment\PaymentService;
use Encore\Admin\Actions\RowAction;

class CancelPayment extends RowAction
{
    public $name = 'Отменить платеж';

    public function handle(
      OnlinePayment $payment
    ) {
        $paymentService = new PaymentService;
        $result = $paymentService->cancelOnlinePayment($payment);
        if(OnlinePaymentStatusEnum::tryFrom($result->last_status_enum_id) === OnlinePaymentStatusEnum::CANCELED) {
          return $this->response()->success('Платеж отменен!')->refresh();
        } else {
          return $this->response()->error('Ошибка при отмене платежа!')->refresh();
        }
    }

    public function dialog()
    {
        $this->confirm('Отменить платеж?');
    }
}
