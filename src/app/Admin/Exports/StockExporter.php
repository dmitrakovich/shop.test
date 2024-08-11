<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Row;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class StockExporter extends ExcelExporterFromCollection implements WithDrawings, WithEvents
{
    /**
     * @var string
     */
    protected $fileName = 'Склад.xlsx';

    /**
     * Create a new exporter instance.
     */
    public function __construct(?Grid $grid = null)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(60);

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
            return array_map(fn ($name) => $this->prepareRow($name, $row), $columns);
        });
    }

    /**
     * Prepare a data row for the specified column.
     */
    private function prepareRow(string $columnName, Row $row): mixed
    {
        if ($columnName === 'media') {
            return null;
        }
        $data = $row->column($columnName);
        if (str_starts_with($columnName, 'stock_')) {
            $data = strip_tags($data);
        }

        return $data;
    }

    /**
     * Setup height, wedth, color & other styles
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $columnCount = $this->grid->getColumns()->count();
                $rowCount = $this->grid->rows()->count() + 1;
                $maxColumnLetter = Coordinate::stringFromColumnIndex($columnCount);

                $sheet = $event->sheet->getDelegate();

                $sheet->freezePane('A2');

                $titleStyles = $sheet->getStyle(1);
                $titleStyles->getFont()->setBold(true)->setSize(12);

                $borderStyle = $sheet->getStyle("A1:{$maxColumnLetter}{$rowCount}")->getBorders();
                $borderStyle->getAllBorders()->setBorderStyle(Style\Border::BORDER_THIN);

                $alignmentStyle = $sheet->getStyle("A1:{$maxColumnLetter}{$rowCount}")->getAlignment();
                $alignmentStyle->setVertical(Style\Alignment::VERTICAL_CENTER);

                foreach ($sheet->getRowIterator(2) as $row) {
                    $sheet->getRowDimension($row->getRowIndex())->setRowHeight(82);
                }

                $sheet->getColumnDimension('A')->setWidth(16); // фото
                $sheet->getColumnDimension('B')->setWidth(45); // название
                for ($i = 3; $i < $columnCount - 3; $i++) {
                    $sheet->getColumnDimensionByColumn($i)->setWidth(20); // склады
                }
                $sheet->getColumnDimensionByColumn($columnCount - 3)->setWidth(25); // размеры на сайте
                $sheet->getColumnDimensionByColumn($columnCount - 2)->setWidth(14); // цена в 1С
                $sheet->getColumnDimensionByColumn($columnCount - 1)->setWidth(14); // цена на сайте
                $sheet->getColumnDimensionByColumn($columnCount)->setWidth(10); // скидка

                $this->grid->rows()->map(function (Row $row) use ($maxColumnLetter, $sheet) {
                    $attr = $row->getRowAttributes();
                    if (($pos = strpos($attr, 'background-color: #')) !== false) {
                        $rowNumber = $row->number + 2;
                        $rgb = substr($attr, $pos + 19, 6);
                        $sheet->getStyle("A{$rowNumber}:{$maxColumnLetter}{$rowNumber}")
                            ->getFill()
                            ->setFillType(Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB($rgb);
                    }
                });
            },
        ];
    }

    /**
     * Insert product images
     *
     * @return BaseDrawing[]
     */
    public function drawings()
    {
        $images = [];
        $noImagePath = public_path('images/no-image-100.png');

        $this->grid->rows()->map(function (Row $row) use (&$images, $noImagePath) {
            $imgHtml = $row->column('media');
            if (str_contains($imgHtml, 'no-image-100')) {
                $imagePath = $noImagePath;
            } else {
                $start = strpos($imgHtml, 'media/products/');
                $end = strpos($imgHtml, "'", $start);
                $imagePath = public_path(substr($imgHtml, $start, $end - $start));
                if (!file_exists($imagePath)) {
                    $imagePath = $noImagePath;
                }
            }

            $drawing = new Drawing();
            $drawing->setPath($imagePath);
            $drawing->setOffsetX(6)->setOffsetY(5);
            $drawing->setCoordinates('A' . ($row->number + 2));

            $images[] = $drawing;
        });

        return $images;
    }
}
