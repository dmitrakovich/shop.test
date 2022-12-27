<?php

namespace App\Http\Controllers\Shop;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\View\View;
use SeoFacade;

class PaymentController extends BaseController
{
    /**
     * Страница оплаты ЕРИП
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

    /**
     * Страница оплаты Yandex
     */
    public function yandex(
    string $linkCode,
    PaymentService $paymentService
  ) {
        $payment = $paymentService->getPaymentByLinkCode($linkCode);
        if ($payment) {
            SeoFacade::setTitle('Счёт № ' . $payment->payment_num)
              ->setDescription('Счёт № ' . $payment->payment_num)
              ->setRobots('noindex, nofollow');

            return view('shop.payment.yandex', [
                'payment' => $payment,
                'linkCode' => $linkCode,
            ]);
        } else {
            abort(404);
        }
    }

    public function linkCode(
    string $linkCode,
    PaymentService $paymentService
  ) {
        $payment = $paymentService->getPaymentByLinkCode($linkCode);
        if ($payment) {
            SeoFacade::setTitle('Счёт № ' . $payment->payment_num)
              ->setDescription('Счёт № ' . $payment->payment_num)
              ->setRobots('noindex, nofollow');

            return view('shop.payment.payment-link-code', [
                'payment' => $payment,
                'linkCode' => $linkCode,
            ]);
        } else {
            abort(404);
        }
    }

    public function checkLinkCode(
    string $linkCode,
    PaymentService $paymentService
  ) {
        $payment = $paymentService->getPaymentByLinkCode($linkCode);

        return [
            'payment_url' => $payment->payment_url,
        ];
    }
}
