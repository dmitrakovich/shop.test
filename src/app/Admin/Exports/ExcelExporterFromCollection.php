<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExporterFromCollection extends AbstractExporter implements FromCollection, WithHeadings
{
    use Exportable;

    /**
     * @var string
     */
    protected $fileName = 'export.xlsx';

    /**
     * @var array
     */
    protected $headings = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->grid->getColumns()->map(function (Column $column) {
            return $column->getLabel();
        })->toArray();
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->getData(false);
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $this->download($this->fileName)->prepare(request())->send();

        exit;
    }
}
