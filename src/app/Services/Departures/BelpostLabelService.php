<?php

namespace App\Services\Departures;

use App\Helpers\TextHelper;
use App\Models\Orders\Order;
use App\Models\Payments\Installment;
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
        $order->load(['itemsExtended.installment', 'onlinePayments', 'delivery']);

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

        $sheet->setCellValue('D20', null);
        $sheet->setCellValue('D22', null);
        $sheet->setCellValue('D25', null);
        $sheet->setCellValue('D28', null);
        $sheet->setCellValue('D31', null);
        $sheet->setCellValue('D33', null);
        $sheet->setCellValue('D35', null);
        $sheet->setCellValue('D37', null);

        if ($order->delivery->instance === 'BelpostCourierFitting') {
            $sheet->setCellValue('D25', 'P');
            $sheet->setCellValue('D33', 'P');
            $sheet->getStyle('D25')->applyFromArray([
                'font' => [
                    'name' => 'Wingdings 2',
                ],
            ]);
            $sheet->getStyle('D33')->applyFromArray([
                'font' => [
                    'name' => 'Wingdings 2',
                ],
            ]);
        } else {
            $sheet->setCellValue('D22', 'P');
            $sheet->getStyle('D22')->applyFromArray([
                'font' => [
                    'name' => 'Wingdings 2',
                ],
            ]);
        }
        if ((int)$order->payment_id === Installment::PAYMENT_METHOD_ID) {
            $sheet->setCellValue('D31', 'P');
            $sheet->getStyle('D31')->applyFromArray([
                'font' => [
                    'name' => 'Wingdings 2',
                ],
            ]);
        }

        $sheet->setCellValue('BF35', substr(trim($order->phone), -9, -7));
        $sheet->setCellValue('BJ35', preg_replace("/^(\d{3})(\d{2})(\d{2})$/", '$1 $2 $3', substr(trim($order->phone), -7)));

        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        $htmlWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        $htmlWriter->save(public_path('/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.html'));

        return url($resultPath);
    }
}
