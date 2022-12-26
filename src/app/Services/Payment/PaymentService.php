<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Illuminate\Notifications\Facades\SmsTraffic;

use App\Services\Payment\Methods\PaymentEripService;
use App\Services\Payment\Methods\PaymentYandexService;

class PaymentService
{
  private array $paymentMethodService = [];

  /**
   * Получить онлайн платеж по id платежа.
   *
   * @param  string  $payment_id
   * @param  OnlinePaymentMethodEnum  $method_enum
   * @return OnlinePayment
   */
  public function getOnlinePaymentByPaymentUrl(string $payment_url, OnlinePaymentMethodEnum $method_enum): ?OnlinePayment
  {
    return OnlinePayment::where('payment_url', $payment_url)->where('method_enum_id', $method_enum)->with('order')->first();
  }

  /**
   * Получить сервис способа оплаты по его enum.
   *
   * @param OnlinePaymentMethodEnum $paymentMethodEnum
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
   * Создать онлайн платеж.
   *
   * @param  array  $data
   * @return OnlinePayment
   */
  public function createOnlinePayment($data): OnlinePayment
  {
    $order                = Order::where('id', $data['order_id'])->with('itemsExtended')->first();
    $paymentCount         = OnlinePayment::where('order_id', $data['order_id'])->count();
    $payment_num          = $order->id . '-' . (++$paymentCount);
    $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($data['method_enum_id']));
    $onlinePayment = $paymentMethodService->create($order, $data['amount'], $payment_num, $data);
    if (isset($data['send_sms']) && $data['send_sms'] == 1) {
      $smsText = ($order->first_name ? ($order->first_name . ', ') : '') . 'Вам выставлен счет № ' . $payment_num . ' - подробнее по ссылке ' . route('pay.erip', $payment_num, true);
      SmsTraffic::send($order->phone, $smsText);
    }
    return $onlinePayment;
  }

  /**
   * Получить платеж по коду ссылки.
   *
   * @param  string  $linkCode
   * @return OnlinePayment
   */
  public function getPaymentByLinkCode(string $linkCode): ?OnlinePayment
  {
    return OnlinePayment::where('link_code', $linkCode)->first();
  }

  /**
   * Создать QRcode платежа.
   *
   * @param  OnlinePayment  $onlinePayment
   * @return OnlinePayment
   */
  public function createOnlinePaymentQrCode(OnlinePayment $onlinePayment): OnlinePayment
  {
    $paymentMethodService = $this->getPaymentMethodServiceByEnum(OnlinePaymentMethodEnum::tryFrom($onlinePayment->method_enum_id));
    return $paymentMethodService->createQrCode($onlinePayment);
  }
}
