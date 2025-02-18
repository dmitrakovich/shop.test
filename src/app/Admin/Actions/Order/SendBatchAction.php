<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Batch;
use App\Services\Departures\BatchService;
use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;

class SendBatchAction extends RowAction
{
    public $name = 'Отправить партию';

    public function handle(Batch $batch): Response
    {
        $file = app(BatchService::class)->createBatchCsv($batch);

        $batch->touch('dispatch_date');

        return $this->response()->success('Партия успешно отправлена!')->download($file);
    }
}
