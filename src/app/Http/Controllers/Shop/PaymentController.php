<?php

namespace App\Http\Controllers\Shop;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\View\View;
use SeoFacade;

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

        SeoFacade::setTitle('Счёт № ' . $online_payment->payment_num)
            ->setDescription('Счёт № ' . $online_payment->payment_num)
            ->setRobots('noindex, nofollow');

        return view('shop.payment.erip', ['online_payment' => $online_payment]);
    }
}
