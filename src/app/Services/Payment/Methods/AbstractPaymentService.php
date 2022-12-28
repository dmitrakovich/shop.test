<?php

namespace App\Services\Payment\Methods;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;

abstract class AbstractPaymentService
{
    /**
     * Create new payment
     *
     * @param  Order  $order
     * @param  float  $amount
     * @param  string|null  $paymentNum
     * @return OnlinePayment
     */
    abstract public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): OnlinePayment;

    /**
     * Get payment by payment_id.
     *
     * @param  string  $paymentId
     * @param  OnlinePaymentMethodEnum  $methodEnum
     * @return OnlinePayment
     */
    public function getOnlinePaymentByPaymentId(
      string $paymentId,
      ?OnlinePaymentMethodEnum $methodEnum = null
    ): ?OnlinePayment {
        $result = OnlinePayment::where('payment_id', $paymentId);
        if ($methodEnum) {
            $result = $result->where('method_enum_id', $methodEnum);
        }

        return $result->first();
    }

    /**
     * Set OnlinePayment status.
     *
     * @param  OnlinePayment  $payment
     * @param  OnlinePaymentStatusEnum  $status
     * @return OnlinePayment
     */
    public function setPaymentStatus(
      OnlinePayment $payment,
      OnlinePaymentStatusEnum $status
    ): OnlinePayment {
        if ($payment->last_status_enum_id !== $status->value) {
            $payment->last_status_enum_id = $status->value;
            $payment->save();
            $payment->statuses()->create([
                'admin_user_id' => Admin::user() ? Admin::user()->id : null,
                'payment_status_enum_id' => $status->value,
            ]);
        }

        return $payment;
    }
}