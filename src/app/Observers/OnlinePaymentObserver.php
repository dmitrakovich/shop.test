<?php

namespace App\Observers;

use App\Models\Payments\OnlinePayment;
use App\Services\Payment\PaymentService;

class OnlinePaymentObserver
{
    /**
     * Handle the OnlinePayment "created" event.
     */
    public function saved(
        OnlinePayment $onlinePayment
    ): void {
        $paymentService = app(PaymentService::class);
        $paymentService->autoSetOrderStatus($onlinePayment);
    }
}
