<?php

namespace App\Services\Payment;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Payments\Installment;
use App\Services\Imap\ImapParseEmailService;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BelpostCODService
{
    /**
     * Imports an Excel file to process COD payments.
     *
     * @param  UploadedFile  $file The uploaded Excel file.
     * @return array The result of the import process, including the count and sum of payments.
     */
    public function importExcelCOD(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $currentRow = 1;
        $parsedData = [];
        $result = [
            'count' => 0,
            'sum' => 0,
        ];

        while (($currentRow <= $sheet->getHighestRow())) {
            $trackCell = trim($sheet->getCell('F' . $currentRow)->getValue());
            preg_match('/№(.*?),/', $trackCell, $currentTrack);
            $currentTrack = $currentTrack[1] ?? null;
            if ($currentTrack) {
                $parsedData[$currentTrack] = trim($sheet->getCell('C' . $currentRow)->getValue());
            }
            $currentRow++;
        }

        $orders = Order::with([
            'onlinePayments',
            'data' => fn ($query) => $query->with('installment'),
            'track',
        ])->whereHas('track', fn ($query) => $query->whereIn('track_number', array_keys($parsedData)))
            ->get();
        foreach ($orders as $order) {
            $orderTrackNumber = $order->track->track_number;
            $paymentSum = (float)($parsedData[$orderTrackNumber] ?? 0);
            if ($paymentSum && !count($order->onlinePayments->where('amount', $paymentSum))) {
                $payment = $order->onlinePayments()->create([
                    'currency_code' => 'BYN',
                    'currency_value' => 1,
                    'amount' => $paymentSum,
                    'paid_amount' => $paymentSum,
                    'method_enum_id' => OnlinePaymentMethodEnum::COD,
                    'last_status_enum_id' => OnlinePaymentStatusEnum::SUCCEEDED,
                ]);
                $payment->statuses()->create([
                    'payment_status_enum_id' => OnlinePaymentStatusEnum::SUCCEEDED,
                ]);

                if (in_array($order->status_key, ['fitting', 'sent'])) {
                    $partialBuybackItemsCount = 0;
                    $isInstallment = $order->payment_id === Installment::PAYMENT_METHOD_ID;
                    $successfulPaymentsSum = $order->onlinePayments->where('last_status_enum_id', OnlinePaymentStatusEnum::SUCCEEDED)->sum('amount');
                    $itemCodSum = (float)($paymentSum + ($successfulPaymentsSum / count($order->data)));
                    $orderTotalPrice = (float)($order->getItemsPrice() - $successfulPaymentsSum);
                    $firstPaymentsSum = $isInstallment ? $order->data->map(function ($item) use ($isInstallment, $itemCodSum, &$partialBuybackItemsCount) {
                        $firstPaymentSum = $isInstallment ? $item->current_price - ($item->installment->monthly_fee * 2) : 0;
                        if ($item->current_price == $itemCodSum || ($isInstallment && $firstPaymentSum == $itemCodSum)) {
                            ++$partialBuybackItemsCount;
                        }
                        return $firstPaymentSum;
                    })->sum() : 0;
                    $firstPaymentsSum = $firstPaymentsSum ? ($firstPaymentsSum - $successfulPaymentsSum) : 0;

                    if (
                        $orderTotalPrice == $paymentSum ||
                        ($isInstallment && $firstPaymentsSum == $paymentSum)
                    ) {
                        $order->update(['status_key' => 'complete']);
                        $order->data->each(function (OrderItem $orderItem) {
                            $orderItem->update(['status_key' => 'complete']);
                        });
                    } elseif ($partialBuybackItemsCount === 1) {
                        $order->update(['status_key' => 'delivered']);
                        $order->data->each(function (OrderItem $orderItem) use ($order, $itemCodSum, $isInstallment) {
                            $firstPaymentSum = $isInstallment ? $orderItem->current_price - ($orderItem->installment->monthly_fee * 2) : 0;
                            if (
                                $orderItem->current_price == $itemCodSum ||
                                $isInstallment && $firstPaymentSum == $itemCodSum
                            ) {
                                $orderItem->update(['status_key' => 'complete']);
                            } else {
                                $productFullName = $orderItem->product->getFullName();
                                $order->adminComments()->create([
                                    'comment' => "Товар {$productFullName} не выкуплен - ожидайте возврат",
                                ]);
                            }
                        });
                    } else {
                        $order->update(['status_key' => 'delivered']);
                        $order->adminComments()->create([
                            'comment' => "Получен наложенный платеж на сумму {$paymentSum}. Распределите сумму по товарам!",
                        ]);
                    }
                }
                $result['count']++;
                $result['sum'] += $paymentSum;
            }
        }

        return $result;
    }

    /**
     * Parses the email and imports the COD Excel file into the system.
     */
    public function parseEmail(): bool
    {
        $imapConfigHost = config('services.imap.belpost_host');
        $imapConfigUser = config('services.imap.belpost_user');
        $imapConfigPass = config('services.imap.belpost_password');
        if ($imapConfigHost && $imapConfigUser && $imapConfigPass) {
            $parseEmailService = new ImapParseEmailService($imapConfigHost, $imapConfigUser, $imapConfigPass);
            $periods = new DatePeriod(
                new DateTime(date('Y-m-d', strtotime('-2 day'))),
                new DateInterval('P1D'),
                new DateTime(date('Y-m-d', strtotime('now')))
            );
            foreach ($periods as $period) {
                $mails = $parseEmailService->getMessagesByDate('barocco.by', $period->format('Y-m-d'));
                if (!empty($mails)) {
                    foreach ($mails as $mail) {
                        $subject = $mail->getSubject();
                        if (str_contains($subject, 'Приложение к ППИ от Брестского филиала РУП')) {
                            $attachments = $mail->getAttachments();
                            foreach ($attachments as $attachment) {
                                $path = storage_path('app/public/belpost/cod/' . date('d-m-Y') . '/') . $attachment->getFilename();
                                File::ensureDirectoryExists(dirname($path));
                                File::put($path, $attachment->getDecodedContent());
                                $this->importExcelCOD(new UploadedFile($path, $attachment->getFilename()));
                            }
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }
}
