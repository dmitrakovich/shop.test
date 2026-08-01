<?php

namespace App\Http\Controllers\Api;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\OnlinePaymentPageResource;
use App\Services\Payment\PaymentService;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * ERIP / E-POS payment page data.
     */
    public function erip(string $paymentUrl): OnlinePaymentPageResource
    {
        $payment = $this->paymentService->getOnlinePaymentByPaymentUrl(
            $paymentUrl,
            OnlinePaymentMethodEnum::ERIP
        );

        abort_if($payment === null, 404);

        return new OnlinePaymentPageResource($payment);
    }

    /**
     * YooKassa intro payment page data.
     */
    public function yandex(string $linkCode): OnlinePaymentPageResource
    {
        $payment = $this->paymentService->getPaymentByLinkCode($linkCode);

        abort_if(
            $payment === null || $payment->method_enum_id !== OnlinePaymentMethodEnum::YANDEX,
            404
        );

        $payment->loadMissing('order');

        return new OnlinePaymentPageResource($payment);
    }

    /**
     * Resolve YooKassa confirmation URL by link code.
     *
     * @return array{payment_url: string|null}
     */
    public function resolveLinkCode(string $linkCode): array
    {
        $payment = $this->paymentService->getPaymentByLinkCode($linkCode);

        abort_if($payment === null, 404);

        return [
            'payment_url' => $payment->payment_url,
        ];
    }
}
