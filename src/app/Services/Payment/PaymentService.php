<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use App\Services\Payment\Methods\PaymentEripService;
use App\Services\Payment\Methods\PaymentYandexService;
use Illuminate\Notifications\Facades\SmsTraffic;

class PaymentService
{
    private array $paymentMethodService = [];

    /**
     * Get payment by payment_url.
     *
     * @param  string  $payment_url
     * @param  OnlinePaymentMethodEnum  $method_enum
     * @return OnlinePayment
     */
    public function getOnlinePaymentByPaymentUrl(string $payment_url, OnlinePaymentMethodEnum $method_enum): ?OnlinePayment
    {
        return OnlinePayment::where('payment_url', $payment_url)->where('method_enum_id', $method_enum)->with('order')->first();
    }

    /**
     * Get payment method service by enum.
     *
     * @param  OnlinePaymentMethodEnum  $paymentMethodEnum
     */
    private function getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum $paymentMethodEnum)
    {
        switch ($paymentMethodEnum) {
            case OnlinePaymentMethodEnum::ERIP:
                return $this->paymentMethodService[OnlinePaymentMethodEnum::ERIP->value] = $this->paymentMethodService[OnlinePaymentMethodEnum::ERIP->value] ?? new PaymentEripService;
                break;
            case OnlinePaymentMethodEnum::YANDEX:
                return $this->paymentMethodService[OnlinePaymentMethodEnum::YANDEX->value] = $this->paymentMethodService[OnlinePaymentMethodEnum::YANDEX->value] ?? new PaymentYandexService;
                break;
        }
    }

    /**
     * Create OnlinePayment.
     *
     * @param  array  $data
     * @return OnlinePayment
     */
    public function createOnlinePayment($data): OnlinePayment
    {
        $order = Order::where('id', $data['order_id'])->with('itemsExtended')->first();
        $paymentCount = OnlinePayment::where('order_id', $data['order_id'])->count();
        $payment_num = $order->id . '-' . (++$paymentCount);
        $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($data['method_enum_id']));
        $onlinePayment = $paymentMethodService->create($order, $data['amount'], $payment_num, $data);
        if (isset($data['send_sms']) && $data['send_sms'] == 1) {
            $smsText = ($order->first_name ? ($order->first_name . ', ') : '') . 'Вам выставлен счет № ' . $payment_num . ' - подробнее по ссылке ' . route('pay.erip', $payment_num, true);
            SmsTraffic::send($order->phone, $smsText);
        }

        return $onlinePayment;
    }

    /**
     * Cancel payment.
     *
     * @param  OnlinePayment  $payment
     * @return OnlinePayment
     */
    public function cancelOnlinePayment(OnlinePayment $payment)
    {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($payment->method_enum_id));

        return $paymentMethodService->cancel($payment);
    }

    /**
     * Capture payment.
     *
     * @param  OnlinePayment  $payment
     * @param  float|nulls  $amount
     * @return OnlinePayment
     */
    public function captureOnlinePayment(
        OnlinePayment $payment,
        ?float $amount = null
    ): OnlinePayment {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($payment->method_enum_id));

        return $paymentMethodService->capture($payment, $amount);
    }

    /**
     * Get OnlinePayment by link_code
     *
     * @param  string  $linkCode
     * @return OnlinePayment
     */
    public function getPaymentByLinkCode(string $linkCode): ?OnlinePayment
    {
        return OnlinePayment::where('link_code', $linkCode)->first();
    }

    /**
     * Create payment QRcode.
     *
     * @param  OnlinePayment  $onlinePayment
     * @return OnlinePayment
     */
    public function createOnlinePaymentQrCode(OnlinePayment $onlinePayment): OnlinePayment
    {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($onlinePayment->method_enum_id));

        return $paymentMethodService->createQrCode($onlinePayment);
    }

    /**
     * Webhook handler.
     *
     * @param  array  $requestData
     * @param  OnlinePaymentMethodEnum  $paymentMethodEnum
     * @return bool
     */
    public function webhookHandler(
      array $requestData,
      OnlinePaymentMethodEnum $paymentMethodEnum
    ): bool {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum($paymentMethodEnum);

        return $paymentMethodService->webhookHandler($requestData);
    }
}
