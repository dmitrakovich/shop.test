<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Row;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderItemsExporter extends ExcelExporterFromCollection implements WithEvents
{
    protected $fileName = 'Товары в заказах.xlsx';

    /**
     * Create a new exporter instance.
     */
    public function __construct(?Grid $grid = null)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(90);

        parent::__construct($grid);
    }

    /**
     * Prepare rows for excel
     */
    public function prepareRows(): Collection
    {
        $this->grid->build();
        $columns = $this->grid->visibleColumnNames();

        return $this->grid->rows()->map(function (Row $row) use ($columns) {
            return array_map(fn ($name) => $row->column($name), $columns);
        });
    }

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
