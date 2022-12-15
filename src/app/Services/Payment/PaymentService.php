<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Jobs\Payment\CreateQrcodeJob;
use App\Libraries\HGrosh\Facades\ApiHGroshFacade;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;
use Illuminate\Notifications\Facades\SmsTraffic;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
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
     * Создать онлайн платеж.
     *
     * @param  array  $data
     * @return OnlinePayment
     */
    public function createOnlinePayment($data): OnlinePayment
    {
        $order = Order::where('id', $data['order_id'])->with('itemsExtended')->first();
        $paymentCount = OnlinePayment::where('order_id', $data['order_id'])->count();

        switch (OnlinePaymentMethodEnum::tryFrom($data['method_enum_id'])) {
            case OnlinePaymentMethodEnum::ERIP:
                $config = config('hgrosh');
                $postData = [];
                $payment_num = $order->id . '-' . (++$paymentCount);

                $postData['number'] = $payment_num;
                $postData['currency'] = 933;
                $postData['merchantInfo']['serviceId'] = $config['serviceid'];
                $postData['merchantInfo']['retailOutlet']['code'] = $config['retailoutletcode'];
                $postData['dateInAirUTC'] = now();
                $postData['paymentDueTerms']['termsDay'] = 3;
                $postData['paymentRules']['isTariff'] = false;

                $postDataItems = [];
                $postDataItems[] = [
                    'name' => 'Заказ №' . $payment_num,
                    'quantity' => 1,
                    'measure' => '',
                    'unitPrice' => ['value' => $data['amount']],
                ];

                $postData['items'] = $postDataItems;
                $postData['billingInfo']['contact']['firstName'] = $order->first_name;
                $postData['billingInfo']['contact']['lastName'] = $order->last_name;
                $postData['billingInfo']['contact']['middleName'] = $order->patronymic_name;
                $postData['billingInfo']['phone']['nationalNumber'] = preg_replace('/[^0-9]/', '', $order->phone);
                $postData['billingInfo']['email'] = $order->email;
                $postData['billingInfo']['address']['line1'] = $order->user_addr;

                $payment = ApiHGroshFacade::invoicingCreateInvoice()->addGetParam([
                    'canPayAtOnce' => 'true',
                ])->request($postData);
                if ($payment->isOk()) {
                    $response = $payment->getBodyFormat();
                    $onlinePayment = OnlinePayment::create([
                        'order_id' => $order->id,
                        'currency_code' => 933,
                        'currency_value' => 1,
                        'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
                        'admin_user_id' => Admin::user()->id ?? null,
                        'amount' => $response[0]['totalAmount'] ?? $data['amount'] ?? null,
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+3 day')),
                        'payment_id' => $response[0]['id'],
                        'payment_num' => $payment_num,
                        'payment_url' => $payment_num,
                        'email' => $response[0]['billingInfo']['email'] ?? null,
                        'phone' => $response[0]['billingInfo']['phone']['fullNumber'] ?? null,
                        'fio' => $response[0]['billingInfo']['contact']['fullName'] ?? null,
                        'comment' => $data['comment'] ?? null,
                    ]);
                    CreateQrcodeJob::dispatch($onlinePayment)->delay(now()->addSeconds(10));
                    if (isset($data['send_sms']) && $data['send_sms'] == 1) {
                        $smsText = ($order->first_name ? ($order->first_name . ', ') : '') . 'Вам выставлен счет № ' . $payment_num . ' - подробнее по ссылке ' . route('pay.erip', $payment_num, true);
                        $smsResponse = SmsTraffic::send($order->phone, $smsText);
                    }
                }
                break;
        }

        return $onlinePayment;
    }

    /**
     * Создать QRcode платежа.
     *
     * @param  OnlinePayment  $onlinePayment
     * @return OnlinePayment
     */
    public function createOnlinePaymentQrCode(OnlinePayment $onlinePayment): OnlinePayment
    {
        $qrCode = ApiHGroshFacade::invoicingInvoiceQRcode()->request([
            'id' => $onlinePayment->payment_id,
            'getImage' => 'true',
        ]);
        if ($qrCode->isOk()) {
            $responseQrCode = $qrCode->getBodyFormat();
            $qrCodePath = 'hgrosh/' . date('m-Y') . '/' . $onlinePayment->payment_id . '.jpg';
            Storage::disk('public')->put($qrCodePath, base64_decode($responseQrCode['result']['image']));
            $onlinePayment->update(['qr_code' => $qrCodePath]);
        }

        return $onlinePayment;
    }
}
