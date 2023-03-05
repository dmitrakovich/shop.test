<?php

namespace App\Admin\Exports;

use Encore\Admin\Grid\Exporters\ExcelExporter;

class StockExporter extends ExcelExporter
{
    protected $fileName = 'Usres test excel.xlsx';

    /**
     * @var array
     */
    protected $headings = [];

    protected $columns = [
        'id' => 'ID',
        'first_name' => 'Имя',
        'phone' => 'Телефон',
    ];
}
