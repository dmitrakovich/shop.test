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
        $resultPath = '/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $spreadsheet = IOFactory::load(storage_path('app/belpost_label_template.xlsx'));
        $firstName = ($order->first_name ?? $order->user->first_name ?? null);
        $lastName = ($order->last_name ?? $order->user->last_name ?? null);
        $patronymicName = ($order->patronymic_name ?? $order->user->patronymic_name ?? null);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('BH3', $order->id . '/ЭЛС');
        $sheet->setCellValue('BP3', date('m/d/y', strtotime($order->created_at)));

        $sheet->setCellValue('BB6', TextHelper::numberToMoneyShortString($order->getTotalPrice()));
        $sheet->setCellValue('AN7', TextHelper::numberToMoneyString($order->getTotalPrice()));

        $sheet->setCellValue('AS22', $lastName);
        $sheet->setCellValue('BF22', $firstName);
        $sheet->setCellValue('BS22', $patronymicName);

        $sheet->setCellValue('AW25', $order->user_addr ?? null);
        $sheet->setCellValue('BC28', $order->city ?? null);

        $sheet->setCellValue('BF35', substr(trim($order->phone), -9, -7));
        $sheet->setCellValue('BJ35', substr(trim($order->phone), -7));

        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        $htmlWriter = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
        $htmlWriter->save(public_path('/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.html'));

        return url($resultPath);
    }
}
