<?php

namespace App\Admin\Actions\Order;

use App\Services\Order\OrdersDistributionService;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class DistributeOrderAction extends BatchAction
{
    public $name = 'Распределить заказ';

    public function handle(
        Collection $collection
    ) {
        $ordersDistributionService = app(OrdersDistributionService::class);
        foreach ($collection as $model) {
            $ordersDistributionService->distributeOrder($model);
        }
        return $this->response()->success('Заказы распределены')->refresh();
    }
}
