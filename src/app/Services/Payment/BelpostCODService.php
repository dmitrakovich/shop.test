<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BelpostCODService
{

    /**
     * Imports an Excel file to process COD payments.
     *
     * @param UploadedFile $file The uploaded Excel file.
     * @return array The result of the import process, including the count and sum of payments.
     */
    public function importExcelCOD(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $isEndOfRow = false;
        $isStartOfRow = false;
        $currentRow = 1;
        $parsedData = [];
        $result = [
            'count' => 0,
            'sum' => 0
        ];

        while (!$isEndOfRow || $currentRow <= $sheet->getHighestRow()) {
            $currentTrack = trim($sheet->getCell('B' . $currentRow)->getValue());
            if (!$isEndOfRow && preg_match('/^[a-zA-Z0-9]+$/si', $currentTrack)) {
                $isStartOfRow = true;
                $parsedData[$currentTrack] = trim($sheet->getCell('C' . $currentRow)->getValue());
            } else {
                $isEndOfRow = $isStartOfRow ? true : $isEndOfRow;
            }
            ++$currentRow;
        }

        $orders = Order::with([
            'onlinePayments',
            'track'
        ])->whereHas('track', fn ($query) => $query->whereIn('track_number', array_keys($parsedData)))
            ->get();
        foreach ($orders as $order) {
            $paymentSum = (float)($parsedData[$order->track->track_number] ?? 0);
            $orderTrackNumber = $order->track->track_number;
            if ($paymentSum && !count($order->onlinePayments->where('amount', $parsedData[$orderTrackNumber]))) {
                $payment = $order->onlinePayments()->create([
                    'currency_code' => 'BYN',
                    'currency_value' => 1,
                    'amount' => $paymentSum,
                    'paid_amount' => $paymentSum,
                    'method_enum_id' => OnlinePaymentMethodEnum::COD,
                    'last_status_enum_id' => OnlinePaymentStatusEnum::SUCCEEDED
                ]);
                $payment->statuses()->create([
                    'payment_status_enum_id' => OnlinePaymentStatusEnum::SUCCEEDED
                ]);
                $result['count']++;
                $result['sum'] += $paymentSum;
            }
        }
        return $result;
    }
}
