<?php

namespace App\Admin\Actions\Order;

use App\Services\Payment\BelpostCODService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BelpostImportCODAction extends Action
{
    protected $selector = '.belpost-import-cod';

    public $name = 'Импортировать';

    /**
     * Handle the request.
     *
     * @param  Request  $request  the request object
     * @return mixed the response object
     */
    public function handle(
        Request $request
    ) {
        $belpostCODService = new BelpostCODService;
        $result = $belpostCODService->importExcelCOD($request->file);
        $resultText = <<<TEXT
            Импортировано платежей: {$result['count']}
            На сумму: {$result['sum']} BYN
        TEXT;

        return $this->response()->swal()->success($resultText)->refresh();
    }

    public function form()
    {
        $this->file('file', 'Excel файл');
    }

    public function html()
    {
        return '<a class="belpost-import-cod btn btn-success">Импортировать</a>';
    }
}
