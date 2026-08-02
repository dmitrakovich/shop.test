<?php

namespace App\Http\Controllers\Api;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\OnlinePaymentPageResource;
use App\Models\Payments\OnlinePayment;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Carbon;

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
        $payment = $this->findYandexPaymentByLinkCode($linkCode);

        abort_if($payment === null, 404);

        $payment->loadMissing('order');

        return new OnlinePaymentPageResource($payment);
    }

    /**
     * Resolve YooKassa confirmation URL by link code.
     *
     * @return array{payment_url: string}
     */
    public function resolveLinkCode(string $linkCode): array
    {
        $payment = $this->findYandexPaymentByLinkCode($linkCode);

        abort_if($payment === null || blank($payment->payment_url), 404);

        return [
            'payment_url' => $payment->payment_url,
        ];
    }

    private function findYandexPaymentByLinkCode(string $linkCode): ?OnlinePayment
    {
        $payment = $this->paymentService->getPaymentByLinkCode($linkCode);

        if (
            $payment === null
            || $payment->method_enum_id !== OnlinePaymentMethodEnum::YANDEX
            || $this->isLinkExpired($payment)
        ) {
            return null;
        }

        return $payment;
    }

    private function isLinkExpired(OnlinePayment $payment): bool
    {
        if (blank($payment->link_expires_at)) {
            return false;
        }

        return now()->greaterThan(Carbon::parse($payment->link_expires_at));
    }
}
