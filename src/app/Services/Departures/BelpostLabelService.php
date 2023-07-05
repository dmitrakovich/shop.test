<?php

namespace App\Services\Departures;

use App\Helpers\TextHelper;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BelpostLabelService
{
    /**
     * Create label
     */
    public function createLabel(Order $order): string
    {
        $order->load(['itemsExtended.installment', 'onlinePayments']);

        $totalCodSum = $order->getTotalCODSum();
        $resultPath = '/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $spreadsheet = IOFactory::load(public_path('templates/belpost_label_template.xlsx'));
        $firstName = ($order->first_name ?? $order->user->first_name ?? null);
        $lastName = ($order->last_name ?? $order->user->last_name ?? null);
        $patronymicName = ($order->patronymic_name ?? $order->user->patronymic_name ?? null);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('BB6', TextHelper::numberToMoneyShortString($totalCodSum));
        $sheet->setCellValue('AN7', TextHelper::numberToMoneyString($totalCodSum));

        $sheet->setCellValue('AS22', $lastName);
        $sheet->setCellValue('BF22', $firstName);
        $sheet->setCellValue('BS22', $patronymicName);

        $sheet->setCellValue('AT25', $order->user_addr ?? null);
        $sheet->setCellValue('AS28', $order->zip ?? null);
        $sheet->setCellValue('BC28', $order->city ?? null);
        $sheet->setCellValue('AS30', $order->region ?? null);

        $sheet->setCellValue('BF35', substr(trim($order->phone), -9, -7));
        $sheet->setCellValue('BJ35', substr(trim($order->phone), -7));

        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        $htmlWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        $htmlWriter->save(public_path('/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.html'));

        return url($resultPath);
    }
}
