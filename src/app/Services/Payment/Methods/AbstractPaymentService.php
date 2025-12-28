<?php

namespace App\Services\Payment\Methods;

use App\Enums\Order\OrderStatus;
use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Config;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;

abstract class AbstractPaymentService
{
    /**
     * Create new payment
     */
    abstract public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): ?OnlinePayment;

    /**
     * Get payment by payment_id.
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
     */
    public function setPaymentStatus(
        OnlinePayment $payment,
        OnlinePaymentStatusEnum $status
    ): OnlinePayment {
        if ($payment->last_status_enum_id !== $status) {
            $payment->last_status_enum_id = $status;
            $payment->save();
            $payment->statuses()->create([
                'admin_user_id' => Admin::user() ? Admin::user()->id : null,
                'payment_status_enum_id' => $status,
            ]);
            $autoOrderStatuses = (bool)(Config::findCacheable('auto_order_statuses')['active'] ?? false);
            if ($autoOrderStatuses && $status === OnlinePaymentStatusEnum::SUCCEEDED) {
                $payment->load('order.items');
                $payment->order->update(['status' => OrderStatus::PAID]);
                $payment->order->items->whereIn('status_key', ['new', 'reserved', 'collect', 'pickup'])
                    ->each(function (OrderItem $orderItem) {
                        $orderItem->update(['status_key' => 'confirmed']);
                    });
            }
        }

        return $payment;
    }
}
