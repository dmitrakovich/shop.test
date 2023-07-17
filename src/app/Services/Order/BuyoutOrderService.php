<?php

namespace App\Services\Order;

use App\Helpers\TextHelper;
use App\Models\Orders\Order;
use App\Models\Payments\Installment;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BuyoutOrderService
{
    /**
     * Create buyout form
     *
     * @return string
     */
    public function createBuyoutForm(int $orderId)
    {
        $resultPath = '/storage/order_buyout/' . $orderId . '.xlsx';
        File::ensureDirectoryExists(dirname(public_path($resultPath)));
        $order = Order::where('id', $orderId)->with(['itemsExtended', 'delivery'])->first();
        $spreadsheet = IOFactory::load(public_path('templates/buyout_template.xlsx'));

        $firstName = ($order->first_name ?? $order->user->first_name ?? null);
        $lastName = ($order->last_name ?? $order->user->last_name ?? null);
        $patronymicName = ($order->patronymic_name ?? $order->user->patronymic_name ?? null);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('S13', $lastName);
        $sheet->setCellValue('AK13', $firstName);
        $sheet->setCellValue('AY13', $patronymicName);
        $sheet->setCellValue('L14', $order->user_addr ?? null);
        $sheet->setCellValue('BK14', $order->city ?? null);

        $sheet->setCellValue('M24', $lastName);
        $sheet->setCellValue('X24', $firstName);
        $sheet->setCellValue('AI24', $patronymicName);
        $sheet->setCellValue('AW24', $order->user_addr ?? null);
        $sheet->setCellValue('CR24', $order->city ?? null);

        $sheet->setCellValue('CR42', $order->city ?? null);
        $sheet->setCellValue('AW42', $order->user_addr ?? null);
        $sheet->setCellValue('M42', $lastName);
        $sheet->setCellValue('X42', $firstName);
        $sheet->setCellValue('AI42', $patronymicName);

        $totalSum = 0;
        $uniqItemsCount = $order->getUniqItemsCount();
        foreach ($order->items as $itemKey => $item) {
            $itemPrice = $item->current_price;
            if ((int)$order->payment_id === Installment::PAYMENT_METHOD_ID) {
                $itemPrice = $itemPrice - (round(($itemPrice * 0.3), 2) * 2);
            }
            if($order->delivery->instance === 'BelpostCourierFitting') {
                $itemPrice -= $order->delivery_price / $uniqItemsCount;
            }
            $totalSum += $itemPrice;
            $itemsColNum = (28 + $itemKey);
            $itemsColNumSecond = (46 + $itemKey);
            if ($itemKey > 0) {
                $sheet->insertNewRowBefore($itemsColNumSecond);
                $sheet->getStyle('A' . $itemsColNumSecond . ':DB' . $itemsColNumSecond)->applyFromArray($sheet->getStyle('A28:DB28')->exportArray());
                $sheet->mergeCells('C' . $itemsColNumSecond . ':G' . $itemsColNumSecond);
                $sheet->mergeCells('H' . $itemsColNumSecond . ':AC' . $itemsColNumSecond);
                $sheet->mergeCells('AD' . $itemsColNumSecond . ':AX' . $itemsColNumSecond);
                $sheet->mergeCells('AY' . $itemsColNumSecond . ':BC' . $itemsColNumSecond);
                $sheet->mergeCells('BD' . $itemsColNumSecond . ':BJ' . $itemsColNumSecond);
                $sheet->mergeCells('BK' . $itemsColNumSecond . ':BM' . $itemsColNumSecond);
                $sheet->mergeCells('BN' . $itemsColNumSecond . ':BW' . $itemsColNumSecond);
                $sheet->mergeCells('BX' . $itemsColNumSecond . ':DA' . $itemsColNumSecond);
                $itemsColNumSecond++;

                $sheet->insertNewRowBefore($itemsColNum);
                $sheet->getStyle('A' . $itemsColNum . ':DB' . $itemsColNum)->applyFromArray($sheet->getStyle('A28:DB28')->exportArray());
                $sheet->mergeCells('C' . $itemsColNum . ':G' . $itemsColNum);
                $sheet->mergeCells('H' . $itemsColNum . ':AC' . $itemsColNum);
                $sheet->mergeCells('AD' . $itemsColNum . ':AX' . $itemsColNum);
                $sheet->mergeCells('AY' . $itemsColNum . ':BC' . $itemsColNum);
                $sheet->mergeCells('BD' . $itemsColNum . ':BJ' . $itemsColNum);
                $sheet->mergeCells('BK' . $itemsColNum . ':BM' . $itemsColNum);
                $sheet->mergeCells('BN' . $itemsColNum . ':BW' . $itemsColNum);
                $sheet->mergeCells('BX' . $itemsColNum . ':DA' . $itemsColNum);
            }
            $sheet->setCellValue('C' . $itemsColNum, ($itemKey + 1));
            $sheet->setCellValue('H' . $itemsColNum, ($item->product->sku ?? $item->product->title ?? null));
            $sheet->setCellValue('AD' . $itemsColNum, ($item->product->brand->name ?? null) . ', ' . mb_strtolower($item->product->category->name ?? ''));
            $sheet->setCellValue('AY' . $itemsColNum, ($item->size->name ?? null));
            $sheet->setCellValue('BD' . $itemsColNum, TextHelper::numberToMoneyShortString($itemPrice));
            $sheet->setCellValue('BK' . $itemsColNum, 'коп.');

            $sheet->setCellValue('C' . $itemsColNumSecond, ($itemKey + 1));
            $sheet->setCellValue('H' . $itemsColNumSecond, ($item->product->sku ?? $item->product->title ?? null));
            $sheet->setCellValue('AD' . $itemsColNumSecond, ($item->product->brand->name ?? null) . ', ' . mb_strtolower($item->product->category->name ?? ''));
            $sheet->setCellValue('AY' . $itemsColNumSecond, ($item->size->name ?? null));
            $sheet->setCellValue('BD' . $itemsColNumSecond, TextHelper::numberToMoneyShortString($itemPrice));
            $sheet->setCellValue('BK' . $itemsColNumSecond, 'коп.');
        }

        $sheet->setCellValue('F4', TextHelper::numberToMoneyShortString($totalSum));
        $sheet->setCellValue('V4', TextHelper::numberToMoneyString($totalSum));
        $writer = new Xlsx($spreadsheet);
        $writer->save(public_path($resultPath));

        return url($resultPath);
    }
}
