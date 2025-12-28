<?php

namespace App\Admin\Selectable;

use App\Enums\Order\OrderStatus;
use App\Models\Orders\Order;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Orders extends Selectable
{
    public $model = Order::class;

    public function make()
    {
        $batchId = request()->route('batch');
        $this->model()->whereIn('status', [OrderStatus::PACKAGING, OrderStatus::READY])->where(function ($query) use ($batchId) {
            $query->doesntHave('batch')->orWhereHas('batch', fn ($q) => $q->where('id', $batchId));
        })->orderBy('id', 'desc');

        $this->column('id', 'ID заказа');
        $this->column('admin.name', 'Менеджер');
        $this->column('delivery.name', 'Способ доставки');

        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('id', 'ID');
        });
    }
}
