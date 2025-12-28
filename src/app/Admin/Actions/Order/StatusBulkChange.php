<?php

namespace App\Admin\Actions\Order;

use App\Enums\Order\OrderStatus;
use App\Models\Orders\Order;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StatusBulkChange extends BatchAction
{
    public $name = 'Смена статуса';

    protected $selector = '.js-statusBulkChange';

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function handle(Collection $orders, Request $request): Response
    {
        $orders->toQuery()->update([
            'status' => $request->validate(['status' => 'required|integer'])['status'],
        ]);

        return $this->response()->success('Успешно изменено!')->refresh();
    }

    public function form(): void
    {
        $this->select('status', 'Статус')
            ->options(enum_to_array(OrderStatus::class))
            ->required();
    }

    public function html(): string
    {
        return "<a class='js-statusBulkChange'>$this->name</a>";
    }
}
