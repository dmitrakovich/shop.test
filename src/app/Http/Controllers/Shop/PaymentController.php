<?php

namespace App\Http\Controllers\Shop;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\View\View;

class PaymentController extends BaseController
{
    /**
     * Display the specified product.
     */
    public function erip(
        string $paymentUrl,
        PaymentService $paymentService
    ): View {
        $online_payment = $paymentService->getOnlinePaymentByPaymentUrl($paymentUrl, OnlinePaymentMethodEnum::ERIP);
        if (!$online_payment) {
            abort(404);
        }

        return view('shop.payment.erip', ['online_payment' => $online_payment]);
    }
}
