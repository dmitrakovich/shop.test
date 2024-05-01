<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Payments\OnlinePayment;
use App\Models\Payments\Installment;
use App\Notifications\PaymentSms;
use App\Services\Payment\Methods\PaymentCODService;
use App\Services\Payment\Methods\PaymentEripService;
use App\Services\Payment\Methods\PaymentYandexService;

class PaymentService
{
    private array $paymentMethodService = [];

    /**
     * Get payment by payment_url.
     */
    public function getOnlinePaymentByPaymentUrl(string $payment_url, OnlinePaymentMethodEnum $method_enum): ?OnlinePayment
    {
        return OnlinePayment::where('payment_url', $payment_url)->where('method_enum_id', $method_enum)->with('order')->first();
    }

    /**
     * Get payment method service by enum.
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
            case OnlinePaymentMethodEnum::COD:
                return $this->paymentMethodService[OnlinePaymentMethodEnum::COD->value] = $this->paymentMethodService[OnlinePaymentMethodEnum::COD->value] ?? new PaymentCODService;
                break;
        }
    }

    /**
     * Create OnlinePayment.
     *
     * @param  array  $data
     */
    public function createOnlinePayment($data): OnlinePayment
    {
        $order = Order::where('id', $data['order_id'])->with('itemsExtended')->first();
        $paymentCount = OnlinePayment::where('order_id', $data['order_id'])->count();
        $payment_num = $order->id . '-' . (++$paymentCount);
        $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($data['method_enum_id']));
        $onlinePayment = $paymentMethodService->create($order, $data['amount'], $payment_num, $data);
        if (isset($data['send_sms']) && $data['send_sms'] == 1 && $onlinePayment->link) {
            $order->notify(new PaymentSms($payment_num, $onlinePayment->link, $order->first_name));
        }

        return $onlinePayment;
    }

    /**
     * Create OnlinePayment after order.
     *
     * @param  array  $data
     */
    public function createAfterOrder(Order $order)
    {
    }

    /**
     * Cancel payment.
     *
     * @return OnlinePayment
     */
    public function cancelOnlinePayment(OnlinePayment $payment)
    {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum($payment->method_enum_id);

        return $paymentMethodService->cancel($payment);
    }

    /**
     * Capture payment.
     *
     * @param  float|nulls  $amount
     */
    public function captureOnlinePayment(
        OnlinePayment $payment,
        ?float $amount = null
    ): OnlinePayment {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum($payment->method_enum_id);

        return $paymentMethodService->capture($payment, $amount);
    }

    /**
     * Get OnlinePayment by link_code
     */
    public function getPaymentByLinkCode(string $linkCode): ?OnlinePayment
    {
        return OnlinePayment::where('link_code', $linkCode)->first();
    }

    /**
     * Create payment QRcode.
     */
    public function createOnlinePaymentQrCode(OnlinePayment $onlinePayment): OnlinePayment
    {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum($onlinePayment->method_enum_id);

        return $paymentMethodService->createQrCode($onlinePayment);
    }

    /**
     * Webhook handler.
     */
    public function webhookHandler(
        array $requestData,
        OnlinePaymentMethodEnum $paymentMethodEnum
    ): bool {
        $paymentMethodService = $this->getPaymentMethodServiceByEnum($paymentMethodEnum);

        return $paymentMethodService->webhookHandler($requestData);
    }

    /**
     * Auto sets the order status based on the online payment details.
     *
     * @param OnlinePayment $onlinePayment The online payment object.
     */
    public function autoSetOrderStatus(OnlinePayment $onlinePayment): void
    {
        if (
            $onlinePayment->last_status_enum_id === OnlinePaymentStatusEnum::SUCCEEDED &&
            $onlinePayment->method_enum_id === OnlinePaymentMethodEnum::COD
        ) {
            $order = Order::where('id', $onlinePayment->order_id)
                ->with([
                    'onlinePayments',
                    'data' => fn ($query) => $query->with('installment'),
                ])->first();

            if (in_array($order->status_key, ['fitting', 'sent', 'installment'])) {
                $partialBuybackItemsCount = 0;
                $isInstallment = $order->payment_id === Installment::PAYMENT_METHOD_ID;
                $successfulPaymentsSum = $order->onlinePayments->where('last_status_enum_id', OnlinePaymentStatusEnum::SUCCEEDED)->sum('amount');
                $paymentSum = $onlinePayment->paid_amount;
                $itemCodSum = (float)($paymentSum + ($successfulPaymentsSum / count($order->data)));
                $remainingOrderPayment = (float)($order->getItemsPrice() - $successfulPaymentsSum);

                $firstPaymentsSum = 0;
                foreach ($order->data as $orderItem) {
                    $firstPaymentSum = $isInstallment ? ($orderItem->current_price - ($orderItem->installment->monthly_fee * ($orderItem->installment->num_payments - 1))) : 0;
                    if ($orderItem->current_price == $itemCodSum || ($isInstallment && $firstPaymentSum == $itemCodSum)) {
                        $partialBuybackItemsCount++;
                    }
                    $firstPaymentsSum += $firstPaymentSum;
                }
                $firstPaymentsSum = $firstPaymentsSum ? ($firstPaymentsSum - $successfulPaymentsSum) : 0;

                if(ceil($paymentSum) >= floor($remainingOrderPayment)) {
                    $order->update(['status_key' => 'complete']);
                    $order->data->each(function (OrderItem $orderItem) {
                        $orderItem->update(['status_key' => 'complete']);
                    });
                } elseif (
                    $isInstallment && ceil($firstPaymentsSum) == ceil($paymentSum)
                ) {
                    $order->update(['status_key' => 'installment']);
                    $order->data->each(function (OrderItem $orderItem) {
                        $orderItem->update(['status_key' => 'installment']);
                    });
                } elseif ($partialBuybackItemsCount === 1) {
                    $isPartialComplete = false;
                    $order->data->each(function (OrderItem $orderItem) use ($order, $itemCodSum, $isInstallment, &$isPartialComplete) {
                        $firstPaymentSum = $isInstallment ? $orderItem->current_price - ($orderItem->installment->monthly_fee * ($orderItem->installment->num_payments - 1)) : 0;
                        if (
                            ceil($orderItem->current_price) == ceil($itemCodSum) ||
                            $isInstallment && ceil($firstPaymentSum) == ceil($itemCodSum)
                        ) {
                            $orderItem->update(['status_key' => 'complete']);
                        } else {
                            $productFullName = $orderItem->product->getFullName();
                            $order->adminComments()->create([
                                'comment' => "Товар {$productFullName} не выкуплен - ожидайте возврат",
                            ]);
                            $orderItem->update(['status_key' => 'waiting_refund']);
                            $isPartialComplete = true;
                        }
                    });
                    $order->update(['status_key' => ($isPartialComplete ? 'partial_complete' : 'complete')]);
                } else {
                    $order->update(['status_key' => 'delivered']);
                    $order->data->each(function (OrderItem $orderItem) {
                        $orderItem->update(['status_key' => 'waiting_refund']);
                    });
                    $order->adminComments()->create([
                        'comment' => "Получен наложенный платеж на сумму {$paymentSum}. Распределите сумму по товарам!",
                    ]);
                }
            }
        }
    }
}
