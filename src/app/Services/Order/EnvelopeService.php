<?php

namespace App\Services\Order;

use App\Models\Orders\Order;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EnvelopeService
{
    /**
     * Create envelope
     *
     * @return string
     */
    public function createEnvelope(Order $order)
    {
        $order->loadMissing([
            'user' => fn ($query) => $query->with('lastAddress'),
        ]);

        $resultPath = '/storage/envelope/' . $order->id . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $spreadsheet = IOFactory::load(public_path('templates/envelope_template.xlsx'));

        $firstName = ($order->first_name ?? $order->user->first_name ?? null);
        $lastName = ($order->last_name ?? $order->user->last_name ?? null);
        $patronymicName = ($order->patronymic_name ?? $order->user->patronymic_name ?? null);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('C1', $lastName);
        $sheet->setCellValue('S1', $firstName);
        $sheet->setCellValue('AF1', $patronymicName);

        $sheet->setCellValue('F2', $order?->user?->lastAddress?->street);
        $sheet->setCellValue('AD2', $order?->user?->lastAddress?->house);
        $sheet->setCellValue('AO2', $order?->user?->lastAddress?->corpus);
        $sheet->setCellValue('AW2', $order?->user?->lastAddress?->room);
        $sheet->setCellValue('C3', $order?->user?->lastAddress?->zip);
        $sheet->setCellValue('N3', $order?->user?->lastAddress?->city);
        $sheet->setCellValue('C4', $order?->user?->lastAddress?->district);
        $sheet->setCellValue('C5', $order?->user?->lastAddress?->region);

        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        return url($resultPath);
    }
}
