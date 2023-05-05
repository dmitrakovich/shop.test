<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExporter extends ExcelExporter // implements WithMapping
{
    protected $fileName = 'Склад.xlsx';

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
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function query()
    {
        // !!!!
        return $this->getQuery();
    }

    // public function map($row): array
    // {
    //     return [
    //         $row->product_id
    //     ];

    //     dd($this->grid, $this->grid->rows());
    //     // return [
    //     //     $row->id,
    //     //     $row->name,
    //     //     $row->price,
    //     //     $row->created_at->format('Y-m-d H:i:s'),
    //     // ];
    // }
}
