<?php

namespace App\Services\Payment\Methods;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\NotificationEventType;

class PaymentYandexService extends AbstractPaymentService
{
    private Client $api;

    private bool $isTest;

    public function __construct()
    {
        $paymentConfig = config('payment.payment_systems.yandex');
        $this->api = new Client();
        if (isset($paymentConfig['mode']) && $paymentConfig['mode'] == 'production') {
            $account = $paymentConfig['auth']['account'] ?? null;
            $secure = $paymentConfig['auth']['secure'] ?? null;
            $this->isTest = false;
        } else {
            $account = $paymentConfig['auth']['test_account'] ?? null;
            $secure = $paymentConfig['auth']['test_secure'] ?? null;
            $this->isTest = true;
        }
        $this->api->setAuth($account, $secure);
    }

    /**
     * Create new payment
     */
    public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): OnlinePayment
    {
        $paymentData = [];
        $preAuth = (bool)($data['pre_auth'] ?? false);
        $currencyCode = (string)'RUB';
        $expiresAt = $preAuth ? date('c', strtotime('+7 days')) : null;

        if ($expiresAt) {
            $paymentData['expires_at'] = $expiresAt;
        }
        $paymentData['amount']['value'] = $amount;
        $paymentData['amount']['currency'] = $currencyCode;
        $paymentData['confirmation']['type'] = 'redirect';
        $paymentData['confirmation']['return_url'] = (string)secure_url(config('payment.return_url'));
        $paymentData['capture'] = !$preAuth;
        if ($order->id) {
            $paymentData['description'] = (string)('Оплата заказа № ' . $order->id);
            $paymentData['metadata']['order_id'] = $order->id;
        }
        if (!empty($order->user_full_name)) {
            $paymentData['receipt']['customer']['full_name'] = (string)$order->user_full_name;
        }
        if (!empty($order->email)) {
            $paymentData['receipt']['customer']['email'] = (string)$order->email;
        }
        if (!empty($order->phone)) {
            $paymentData['receipt']['customer']['phone'] = (string)$order->phone;
        }
        $paymentData['test'] = (bool)$this->isTest;
        $payment = $this->api->createPayment($paymentData, uniqid('', true));

        if ($payment) {
            $paymentId = $payment->getId();
            $paymentUrl = $payment->getConfirmation()->getConfirmationUrl();

            $dbData = [];
            $dbData['order_id'] = $order->id;
            $dbData['currency_code'] = $currencyCode;
            $dbData['method_enum_id'] = OnlinePaymentMethodEnum::YANDEX;
            $dbData['amount'] = $amount;
            $dbData['expires_at'] = date('Y-m-d H:i:s', strtotime($expiresAt));
            $dbData['payment_id'] = $paymentId;
            $dbData['payment_num'] = $paymentNum;
            $dbData['payment_url'] = $paymentUrl;
            $dbData['admin_user_id'] = Admin::user() ? Admin::user()->id : null;
            $dbData['fio'] = $payment_info['fio'] ?? null;
            $dbData['phone'] = $payment_info['phone'] ?? null;
            $dbData['email'] = $payment_info['email'] ?? null;
            $dbData['link_code'] = $paymentNum;
            $dbData['link_expires_at'] = date('Y-m-d H:i:s', strtotime('59 minutes'));
            $dbData['is_test'] = (bool)$this->isTest;
            $dbData['request_data'] = $data;

            return OnlinePayment::create($dbData);
        }
    }

    /**
     * Cancel payment
     */
    public function cancel(OnlinePayment $payment): OnlinePayment
    {
        $idempotenceKey = uniqid('', true);
        $response = $this->api->cancelPayment($payment->payment_id, $idempotenceKey);
        if (isset($response->status) && $response->status === 'canceled') {
            $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::CANCELED);
        }

        return $payment;
    }

    /**
     * Capture payment
     */
    public function capture(
        OnlinePayment $payment,
        ?float $amount = null
    ): OnlinePayment {
        $idempotenceKey = uniqid('', true);
        $response = $this->api->capturePayment([
            'amount' => [
                'value' => $amount ?? $payment->amount,
                'currency' => $payment->currency_code,
            ],
        ], $payment->payment_id, $idempotenceKey);
        if (isset($response->status) && $response->status === 'succeeded') {
            $payment->paid_amount = $response->amount->value;
            $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::SUCCEEDED);
        }

        return $payment;
    }

    /**
     * Webhook handler.
     */
    public function webhookHandler(
        array $requestData,
    ): bool {
        $requestData['object']['refundable'] = false;
        $factory = new NotificationFactory();
        $notificationObject = $factory->factory($requestData);
        $responseObject = $notificationObject->getObject();
        $payment = $this->getOnlinePaymentByPaymentId($responseObject->getId(), OnlinePaymentMethodEnum::YANDEX);
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!$this->api->isNotificationIPTrusted($remoteAddr)) {
            return false;
        }

        if ($payment) {
            switch ($notificationObject->getEvent()) {
                case NotificationEventType::PAYMENT_SUCCEEDED:
                    $payment->paid_amount = $responseObject->getAmount()->value ?? null;
                    $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::SUCCEEDED);
                    break;
                case NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE:
                    $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::WAITING_FOR_CAPTURE);
                    break;
                case NotificationEventType::PAYMENT_CANCELED:
                    $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::CANCELED);
                    break;
                default:
                    return false;
            }

            return true;
        } else {
            return false;
        }
    }
}
