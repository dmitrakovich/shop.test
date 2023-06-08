<?php

namespace App\Services\Payment\Methods;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Jobs\Payment\CreateQrcodeJob;
use App\Libraries\HGrosh\Facades\ApiHGroshFacade;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Storage;

class PaymentEripService extends AbstractPaymentService
{
    /**
     * Create new payment
     */
    public function create(Order $order, float $amount, ?string $paymentNum = null, array $data = []): OnlinePayment
    {
        $config = config('hgrosh');
        $postData = [];
        $paymentNum = $paymentNum ?? $order->id;
        $postData['number'] = $paymentNum;
        $postData['currency'] = 933;
        $postData['merchantInfo']['serviceId'] = $config['serviceid'];
        $postData['merchantInfo']['retailOutlet']['code'] = $config['retailoutletcode'];
        $postData['dateInAirUTC'] = now();
        $postData['paymentDueTerms']['termsDay'] = 3;
        $postData['paymentRules']['isTariff'] = false;

        $postDataItems = [];
        $postDataItems[] = [
            'name' => 'Заказ №' . $paymentNum,
            'quantity' => 1,
            'measure' => '',
            'unitPrice' => ['value' => $amount],
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
                'currency_code' => 'BYN',
                'currency_value' => 1,
                'method_enum_id' => OnlinePaymentMethodEnum::ERIP,
                'admin_user_id' => Admin::user() ? Admin::user()->id : null,
                'amount' => $response[0]['totalAmount'] ?? $amount ?? null,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+3 day')),
                'payment_id' => $response[0]['id'],
                'payment_num' => $paymentNum,
                'payment_url' => $paymentNum,
                'email' => $response[0]['billingInfo']['email'] ?? null,
                'phone' => $response[0]['billingInfo']['phone']['fullNumber'] ?? null,
                'fio' => $response[0]['billingInfo']['contact']['fullName'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);
            CreateQrcodeJob::dispatch($onlinePayment)->delay(now()->addSeconds(10));
        } else {
            $response = $payment->getBodyFormat();
            $message = $response['message'] ?? '';
            if (strpos($message, 'с таким номером уже существует') !== false) {
                return $this->create($order, $amount, ++$paymentNum, $data);
            }
        }

        return $onlinePayment;
    }

    /**
     * Update statuses
     */
    public function updateStatuses(): void
    {
        $chunkSize = 50;
        OnlinePayment::where('method_enum_id', OnlinePaymentMethodEnum::ERIP)
            ->whereNotNull('payment_num')
            ->where('last_status_enum_id', OnlinePaymentStatusEnum::PENDING)
            ->chunkById($chunkSize, function ($payments) use ($chunkSize) {
                $searchString = $payments->implode('payment_num', ' || ');
                $paymentsByNum = $payments->mapWithKeys(fn ($item) => [$item->payment_num => $item]);
                $invoicingList = ApiHGroshFacade::invoicingGetListInvoice()->request([
                    'count' => $chunkSize,
                    'searchString' => $searchString,
                ]);
                if ($invoicingList->isOk()) {
                    $responseInvoicingList = $invoicingList->getBodyFormat();
                    $records = $responseInvoicingList['records'] ?? [];
                    foreach ($records as $record) {
                        $payment = $paymentsByNum[$record['number']] ?? null;
                        $state = $record['state'] ?? null;
                        if ($payment && $state) {
                            match ($state) {
                                20 => $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::SUCCEEDED),
                                30, 80, 110 => $this->setPaymentStatus($payment, OnlinePaymentStatusEnum::CANCELED),
                                default => null
                            };
                        }
                    }
                }
            });
    }

    /**
     * Create payment QRcode.
     */
    public function createQrCode(OnlinePayment $onlinePayment): OnlinePayment
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
