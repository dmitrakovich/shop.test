<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class DeleteBatchAction extends RowAction
{
    public $name = 'Расформировать';

    public function handle(Model $model)
    {
        $model->load('orders');
        foreach ($model->orders as $order) {
            Order::where('id', $order->id)->update([
                'status_key' => 'packaging'
            ]);
        }
        $model->delete();
        return $this->response()->success('Партия успешно расформирована!')->refresh();
    }
}
