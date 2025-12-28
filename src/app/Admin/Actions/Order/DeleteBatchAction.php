<?php

namespace App\Admin\Actions\Order;

use App\Enums\Order\OrderStatus;
use App\Models\Orders\Batch;
use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;

class DeleteBatchAction extends RowAction
{
    public $name = 'Расформировать';

    public function handle(Batch $batch): Response
    {
        $batch->orders()->update(['status' => OrderStatus::PACKAGING]);
        $batch->delete();

        return $this->response()->success('Партия успешно расформирована!')->refresh();
    }
}
