<?php

namespace App\Services\Order;

use App\Helpers\TextHelper;
use App\Models\Orders\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InstallmentOrderService
{
    /**
     * Create installment form
     *
     * @return string
     */
    public function createInstallmentForm(Order $order)
    {
        $resultPath = '/storage/order_installments/' . $order->id . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $order->loadMissing([
            'user.passport',
            'items' => fn ($query) => $query
                ->whereHas('status', fn ($q) => $q->where('key', 'pickup'))
                ->with('installment'),
        ]);
        $spreadsheet = IOFactory::load(public_path('templates/installment_template.xlsx'));
        $firstName = ($order->first_name ?? $order->user->first_name ?? null);
        $lastName = ($order->last_name ?? $order->user->last_name ?? null);
        $patronymicName = ($order->patronymic_name ?? $order->user->patronymic_name ?? null);
        $firstSheet = clone $spreadsheet->getActiveSheet();

        foreach ($order->items as $itemKey => $item) {
            if ($itemKey > 0) {
                $spreadsheet->addSheet($firstSheet);
                $spreadsheet->setActiveSheetIndex($itemKey);
            }
            $sheet = $spreadsheet->getActiveSheet()->setTitle('№' . $itemKey + 1);

            $sheet->setCellValue('AD1', $item->installment->contract_number ?? null);
            $sheet->setCellValue('AL3', Carbon::parse(now())->translatedFormat('l, F j, Y'));
            $sheet->setCellValue('I7', $firstName);
            $sheet->setCellValue('T7', $lastName);
            $sheet->setCellValue('AC7', $patronymicName);

            $sheet->setCellValue('C11', ($item->product->brand->name ?? null) . ', ' . mb_strtolower($item->product->category->name ?? ''));
            $sheet->setCellValue('AD11', $item->product->sku ?? $item->product->title ?? null);
            $sheet->setCellValue('G12', $item->size->name ?? null);
            $sheet->setCellValue('H13', TextHelper::numberToMoneyShortString($item->current_price));
            $sheet->setCellValue('V13', TextHelper::numberToMoneyString($item->current_price));

            $sheet->setCellValue('J17', date('m/d/Y', strtotime('now')));
            $sheet->setCellValue('J18', date('m/d/Y', strtotime('+1 month')));
            $sheet->setCellValue('J19', date('m/d/Y', strtotime('+1 month')));
            $sheet->setCellValue('J20', date('m/d/Y', strtotime('+2 month')));

            $sheet->setCellValue('X16', ($item->current_price - (($item->installment->monthly_fee ?? 0) * 2)));
            $sheet->setCellValue('X17', $item->installment->monthly_fee ?? null);
            $sheet->setCellValue('X19', $item->installment->monthly_fee ?? null);

            $sheet->setCellValue('AD16', date('m/d/Y', strtotime('now')));
            $sheet->setCellValue('AD17', date('m/d/Y', strtotime('+1 month')));
            $sheet->setCellValue('AD19', date('m/d/Y', strtotime('+2 month')));

            $sheet->setCellValue('Z33', $firstName);
            $sheet->setCellValue('Z34', $lastName);
            $sheet->setCellValue('AL34', $patronymicName);
            $sheet->setCellValue('AM35', $order->user->passport->series . $order->user->passport->passport_number);
            $sheet->setCellValue('Z37', $order->user->passport->personal_number);
            $sheet->setCellValue('Z39', $order->user->passport->issued_by);
            $sheet->setCellValue('AH41', Carbon::parse($order->user->passport->issued_date)->translatedFormat('l, F j, Y'));

            $sheet->setCellValue('Z43', null);
            $sheet->setCellValue('AK43', null);
            $sheet->setCellValue('AB44', $order->city ?? null);
            $sheet->setCellValue('AC45', $order->user_addr ?? null);
            $sheet->setCellValue('AP45', null);
            $sheet->setCellValue('AW45', null);
            $sheet->setCellValue('AB46', null);
            $sheet->setCellValue('AH47', substr(trim($order->phone), -9, -7));
            $sheet->setCellValue('AL47', substr(trim($order->phone), -7));
            $sheet->setCellValue('AK52', mb_strtoupper(substr($firstName, 0, 1)) . '.' . mb_strtoupper(substr($patronymicName, 0, 1)) . '. ' . $lastName);

            $item->installment->installment_form_file = $resultPath;
            $item->installment->save();
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        return url($resultPath);
    }
}
