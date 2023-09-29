<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid\Row;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderItemsExporter extends AbstractExporter
{
    protected $fileName = 'Товары в заказах.xlsx';

    /**
     * Setup height, wedth, color & other styles
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');

                $titleStyles = $sheet->getStyle(1);
                $titleStyles->getFont()->setBold(true)->setSize(12);

                $sheet->getColumnDimension('A')->setWidth(13);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(36);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(24);
                $sheet->getColumnDimension('G')->setWidth(42);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);

                $this->grid->rows()->map(function (Row $row) use ($sheet) {
                    $cell = 'E' . $row->number + 2;
                    $orderLink = $row->column('order_id');
                    $orderId = substr($orderLink, strpos($orderLink, '>') + 1, -4);

                    $sheet->setCellValue($cell, $orderId);
                    $sheet->getCell($cell)->getHyperlink()->setUrl(
                        route('admin.orders.edit', $orderId, true)
                    );
                });
            },
        ];
    }
}
