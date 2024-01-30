<?php

namespace App\Http\Controllers\Shop;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Services\Payment\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    /**
     * Payment webhook handler.
     *
     * @param  string  $paymentMethod
     * @return response
     */
    public function webhook(
        Request $request,
        $paymentMethod,
        PaymentService $paymentService
    ) {
        $result = null;
        $data = $request->all();
        switch (mb_strtolower($paymentMethod)) {
            case 'yandex':
                $result = $paymentService->webhookHandler($data, OnlinePaymentMethodEnum::YANDEX);

                break;
            default:
                abort(404);
        }

        return $result ? response('ok', 200) : response('Something went wrong', 400);
    }
}
