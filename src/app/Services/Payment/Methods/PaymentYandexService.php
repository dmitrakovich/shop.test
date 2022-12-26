<?php

namespace App\Services\Payment\Methods;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;

use App\Contracts\PaymentMethodContract;

use Encore\Admin\Facades\Admin;

use YooKassa\Client;

class PaymentYandexService implements PaymentMethodContract
{
  private Client $api;
  private bool $isTest;

  public function __construct() {
    $paymentConfig = config('payment.payment_systems.yandex');
    $this->api      = new Client();
    if(isset($paymentConfig['mode']) && $paymentConfig['mode'] == 'production') {
      $account      = $paymentConfig['auth']['account'] ?? null;
      $secure       = $paymentConfig['auth']['secure'] ?? null;
      $this->isTest = false;
    } else {
      $account = $paymentConfig['auth']['test_account'] ?? null;
      $secure  = $paymentConfig['auth']['test_secure'] ?? null;
      $this->isTest = true;
    }
    $this->api->setAuth($account, $secure);
  }

  /**
   * Create new payment
   * @param Order $order
   * @param float $amount
   * @param string|null $paymentNum
   * @return OnlinePayment
   */
  public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): OnlinePayment
  {
    $paymentData     = [];
    $preAuth         = (bool)($data['pre_auth'] ?? false);
    $currencyCode    = (string)'RUB';
    $expiresAt       = $preAuth ? date('c', strtotime('+7 days')) : null;

    if($expiresAt) {
      $paymentData['expires_at']               = $expiresAt;
    }
    $paymentData['amount']['value']            = $amount;
    $paymentData['amount']['currency']         = $currencyCode;
    $paymentData['confirmation']['type']       = 'redirect';
    $paymentData['confirmation']['return_url'] = (string)secure_url(config('payment.return_url'));
    $paymentData['capture']                    = !$preAuth;
    if($order->id) {
      $paymentData['description']          = (string)('Оплата заказа № ' . $order->id);
      $paymentData['metadata']['order_id'] = $order->id;
    }
    if(!empty($order->user_full_name)) {
      $paymentData['receipt']['customer']['full_name'] = (string)$order->user_full_name;
    }
    if(!empty($order->email)) {
      $paymentData['receipt']['customer']['email'] = (string)$order->email;
    }
    if(!empty($order->phone)) {
      $paymentData['receipt']['customer']['phone'] = (string)$order->phone;
    }
    $paymentData['test'] = (bool)$this->isTest;
    $payment              = $this->api->createPayment($paymentData, uniqid('', true));

    if($payment) {
      $paymentId       = $payment->getId();
      $paymentUrl      = $payment->getConfirmation()->getConfirmationUrl();
      $paymentLinkCode = $payment_info['link_code'] ?? uniqid();

      $dbData                      = array();
      $dbData['order_id']          = $order->id;
      $dbData['currency_code']     = $currencyCode;
      $dbData['method_enum_id']    = OnlinePaymentMethodEnum::YANDEX->value;
      $dbData['amount']            = $amount;
      $dbData['expires_at']        = date('Y-m-d H:i:s', strtotime($expiresAt));
      $dbData['payment_id']        = $paymentId;
      $dbData['payment_num']       = $paymentNum;
      $dbData['payment_url']       = $paymentUrl;
      $dbData['admin_user_id']     = Admin::user()->id ?? null;
      $dbData['fio']               = $payment_info['fio']   ?? null;
      $dbData['phone']             = $payment_info['phone'] ?? null;
      $dbData['email']             = $payment_info['email'] ?? null;
      $dbData['link_code']         = $paymentLinkCode;
      $dbData['link_expires_at']   = date('Y-m-d H:i:s', strtotime('59 minutes'));
      $dbData['is_test']           = (bool)$this->isTest;
      $dbData['request_data']      = $data;
      return OnlinePayment::create($dbData);
    }

  }
}
