<?php

namespace App\Services\Payment\Methods;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;

class PaymentCODService extends AbstractPaymentService
{
    /**
     * Create new payment
     */
    public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): OnlinePayment
    {
        $data = [];
        $data['order_id'] = $order->id;
        $data['method_enum_id'] = OnlinePaymentMethodEnum::COD;
        $data['amount'] = $amount;
        $data['payment_num'] = $paymentNum;
        $data['admin_user_id'] = Admin::user() ? Admin::user()->id : null;

        return OnlinePayment::create($data);
    }
}
