<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Row;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AbstractExporter extends ExcelExporterFromCollection implements WithEvents
{
    /**
     * The default file name for the export.
     *
     * @var string
     */
    protected $fileName = 'export.xlsx';

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
     * Set the file name for the current exporter instance.
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = "$fileName.xlsx";

        return $this;
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

                $columnNum = 1;
                $this->grid->columns()->map(function (Column $column) use ($sheet, &$columnNum) {
                    $maxLength = mb_strlen($column->getName());
                    $this->grid->rows()->map(function (Row $row) use ($column, &$maxLength) {
                        $columnData = $row->column($column->getName());
                        if (mb_strlen($columnData) > $maxLength) {
                            $maxLength = mb_strlen($columnData);
                        }
                    });
                    $sheet->getColumnDimensionByColumn($columnNum++)->setWidth($maxLength + 2);
                });
            },
        ];
    }
}
