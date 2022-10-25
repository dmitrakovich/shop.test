<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;

use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;

use App\Libraries\HGrosh\Facades\ApiHGroshFacade;

class PaymentService
{

    /**
     * Получить онлайн платеж по id платежа.
     * @param string $payment_id
     * @param OnlinePaymentMethodEnum $method_enum
     * @return OnlinePayment
     */
    public function getOnlinePaymentByPaymentId(string $payment_id, OnlinePaymentMethodEnum $method_enum): ?OnlinePayment
    {
        return OnlinePayment::where('payment_id', $payment_id)->where('method_enum_id', $method_enum)->with('order')->first();
    }

    /**
     * Создать онлайн платеж.
     * @param array $data
     * @return OnlinePayment
     */
    public function createOnlinePayment($data): OnlinePayment
    {
        $order        = Order::where('id', $data['order_id'])->with('itemsExtended')->first();
        $paymentCount = OnlinePayment::where('order_id', $data['order_id'])->count();

        switch (OnlinePaymentMethodEnum::tryFrom($data['method_enum_id'])) {
            case OnlinePaymentMethodEnum::ERIP:
                $config     = config('hgrosh');
                $postData   = [];
                $payment_id = $order->id . '-' . (++$paymentCount);

                $postData['number']                               = $payment_id;
                $postData['currency']                             = 933;
                $postData['merchantInfo']['serviceId']            = $config['serviceid'];
                $postData['merchantInfo']['retailOutlet']['code'] = $config['retailoutletcode'];
                $postData['paymentDueTerms']['termsDay']          = 3;
                $postData['paymentRules']['isTariff']             = false;

                $postDataItems = [];
                foreach ($order->items as $item) {
                    $postDataItems[] = [
                        'code'       => $item->product->id,
                        'name'       => $item->product->extendedName(),
                        'quantity'   => $item->count,
                        'measure'    => '',
                    ];
                }
                $postData['items']                                  = $postDataItems;
                $postData['billingInfo']['contact']['firstName']    = $order->first_name;
                $postData['billingInfo']['contact']['lastName']     = $order->last_name;
                $postData['billingInfo']['contact']['middleName']   = $order->patronymic_name;
                $postData['billingInfo']['phone']['nationalNumber'] = preg_replace('/[^0-9]/', '', $order->phone);
                $postData['billingInfo']['email']                   = $order->email;
                $postData['billingInfo']['address']['line1']        = $order->user_addr;
                $postData['shippingInfo']['amount']['value']        = $data['amount'];

                $payment = ApiHGroshFacade::invoicingCreateInvoice()->request($postData);
                if ($payment->isOk()) {
                    $response = $payment->getBodyFormat();
                    $onlinePayment = OnlinePayment::create([
                        'order_id'       => $order->id,
                        'currency_code'  => 933,
                        'currency_value' => 1,
                        'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
                        'admin_user_id'  => Admin::user()->id ?? null,
                        'amount'         => $response[0]['totalAmount'] ?? $data['amount'] ?? null,
                        'expires_at'     => date('Y-m-d H:i:s', strtotime('+3 day')),
                        'payment_id'     => $payment_id,
                        'payment_url'    => $payment_id,
                        'email'          => $response[0]['billingInfo']['email'] ?? null,
                        'phone'          => $response[0]['billingInfo']['phone']['fullNumber'] ?? null,
                        'fio'            => $response[0]['billingInfo']['contact']['fullName'] ?? null,
                        'comment'        => $data['comment'] ?? null,
                    ]);
                }
                break;
        }
        return $onlinePayment;
    }
}