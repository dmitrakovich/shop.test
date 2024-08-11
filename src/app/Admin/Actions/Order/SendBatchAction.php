<?php

namespace App\Admin\Actions\Order;

use App\Services\Departures\BatchService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class SendBatchAction extends RowAction
{
    public $name = 'Отправить партию';

    public function handle(Model $model)
    {
        $batchService = new BatchService();
        $file = $batchService->createBatchCsv($model);
        $model->dispatch_date = now();
        $model->save();

        return $this->response()->success('Партия успешно отправлена!')->download($file);
    }
}
