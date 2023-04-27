<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Models\Orders\Batch;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class CreateBatchAction extends BatchAction
{
    public $name        = 'Создать партию';
    protected $selector = '.js-createBatchAction';

    public function handle(Collection $collection)
    {
        $orderIds = $collection->pluck('id');
        if (!empty($orderIds)) {
            $batch = Batch::create();
            Order::whereIn('id', $orderIds)->update([
                'batch_id' => $batch->id
            ]);
        }
        return $this->response()->success('Партия успешно создана!')->refresh();
    }

    public function html()
    {
        return "<a class='js-createBatchAction'>$this->name</a>";
    }
}
