<?php

namespace App\Admin\Actions\Order;

use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Payments\OnlinePayment;
use App\Services\Payment\PaymentService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Http\Request;

class CapturePayment extends RowAction
{
    public $name = 'Подтвердить платеж';

    private OnlinePayment $payment;

    public function __construct(OnlinePayment $payment)
    {
        parent::__construct();
        $this->payment = $payment;
    }

    public function handle(
        OnlinePayment $payment,
        Request $request
    ) {
        $paymentService = new PaymentService;
        $amount = (float)$request->input('amount');
        $result = $paymentService->captureOnlinePayment($payment, $amount);
        if ($result->last_status_enum_id === OnlinePaymentStatusEnum::SUCCEEDED) {
            return $this->response()->success('Платеж подтвержден!')->refresh();
        } else {
            return $this->response()->error('Ошибка при подтверждении платежа!')->refresh();
        }
    }

    public function form()
    {
        $this->text('amount', 'Итоговая сумма, которая спишется с пользователя')->default($this->payment->amount)->rules('required');
    }
}
