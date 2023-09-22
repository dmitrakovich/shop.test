<?php

namespace App\Services\Departures;

use App\Enums\DeliveryTypeEnum;
use App\Helpers\TextHelper;
use App\Models\Orders\Order;
use App\Models\Orders\OrderTrack;
use App\Models\Payments\Installment;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Picqer\Barcode\BarcodeGeneratorJPG;

class BelpostLabelService
{
    /**
     * Label number multipliers
     */
    const MULTIPLIERS = [8, 6, 4, 2, 3, 5, 9, 7];

    /**
     * Create label
     */
    public function createLabel(Order $order): string
    {
        $order->loadMissing([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status_key', ['pickup', 'sent', 'fitting'])
                ->with('installment'),
            'onlinePayments',
            'delivery',
            'user' => fn ($query) => $query->with('lastAddress'),
        ]);

        $totalCodSum = $order->getTotalCODSum();

        $orderTrack = OrderTrack::where(
            fn ($query) => $query->where('order_id', $order->id)->orWhereNull('order_id')
        )->where('delivery_type_enum', DeliveryTypeEnum::BELPOST)
            ->whereNotNull('track_number')
            ->first();
        $barcodePath = '/storage/departures/barcode/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.jpg';
        $resultPath = '/storage/departures/belpost_label/' . date('d-m-Y', strtotime('now')) . '/' . $order->id . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($barcodePath)));
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

        $sheet->setCellValue('AW25', $order->user->lastAddress->street ?? null);
        $sheet->setCellValue('BP25', $order->user->lastAddress->house ?? null);
        $sheet->setCellValue('CB25', $order->user->lastAddress->corpus ?? null);
        $sheet->setCellValue('CI25', $order->user->lastAddress->room ?? null);
        $sheet->setCellValue('AS28', $order->user->lastAddress->zip ?? null);
        $sheet->setCellValue('BC28', $order->user->lastAddress->city ?? null);
        $sheet->setCellValue('AS30', $order->user->lastAddress->district ?? null);
        $sheet->setCellValue('AS33', $order->user->lastAddress->region ?? null);

        $sheet->setCellValue('D20', null);
        $sheet->setCellValue('D22', null);
        $sheet->setCellValue('D25', null);
        $sheet->setCellValue('D28', null);
        $sheet->setCellValue('D31', null);
        $sheet->setCellValue('D33', null);
        $sheet->setCellValue('D35', null);
        $sheet->setCellValue('D37', null);

        $sheet->unmergeCells('AM14:BQ17');
        $sheet->mergeCells('AM14:BF16');
        $sheet->mergeCells('AM17:BF17');
        $sheet->setCellValue('AM17', $orderTrack->track_number);
        $sheet->getStyle('AM17')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $barcodeGeneratorJPG = new BarcodeGeneratorJPG();
        File::put(public_path($barcodePath), $barcodeGeneratorJPG->getBarcode($orderTrack->track_number, $barcodeGeneratorJPG::TYPE_CODE_39, 1, 58));
        $drawing = new Drawing();
        $drawing->setName('Barcode');
        $drawing->setDescription('Barcode');
        $drawing->setPath(public_path($barcodePath));
        $drawing->setCoordinates('AM14');
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

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
        $orderTrack->update([
            'order_id' => $order->id,
        ]);

        return url($resultPath);
    }

    /**
     * Calculate the checksum for a Belarus Post label.
     */
    public function calculateCheckSum(string $labelNumber): int
    {
        if (strlen($labelNumber) !== 8 || !is_numeric($labelNumber)) {
            throw new \Exception('Invalid label number');
        }
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $digit = (int)$labelNumber[$i];
            $sum += $digit * self::MULTIPLIERS[$i];
        }
        $remainder = $sum % 11;
        $result = 11 - $remainder;

        if ($result > 10) {
            return 5;
        } elseif ($result === 10) {
            return 0;
        } else {
            return $result;
        }
    }
}
