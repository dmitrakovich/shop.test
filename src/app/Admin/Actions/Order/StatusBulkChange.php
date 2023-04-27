<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Models\Orders\OrderStatus;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StatusBulkChange extends BatchAction
{
    public $name        = 'Cмена статуса';
    protected $selector = '.js-statusBulkChange';

    public function handle(Collection $collection, Request $request)
    {
        $statusKey = $request->status_key ?? null;
        $orderIds = $collection->pluck('id');
        if ($statusKey && !empty($orderIds)) {
            Order::whereIn('id', $orderIds)->update([
                'status_key' => $statusKey
            ]);
        }
        return $this->response()->success('Успешно изменено!')->refresh();
    }

    public function form()
    {
        $orderStatuses = OrderStatus::ordered()->pluck('name_for_admin', 'key');
        $this->select('status_key', 'Статус')->options($orderStatuses);
    }

    public function html()
    {
        return "<a class='js-statusBulkChange'>$this->name</a>";
    }
}
