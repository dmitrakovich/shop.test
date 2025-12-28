<?php

namespace App\Admin\Actions\Order;

use App\Enums\Order\OrderStatus;
use App\Models\Orders\Order;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;

class ProcessOrder extends RowAction
{
    public $name = 'Взять в работу';

    protected bool $isRow = false;

    /**
     * @return mixed
     */
    public function handle(Order $order)
    {
        $this->isRow = true;

        return $this->process($order);
    }

    /**
     * Handle action
     *
     * @return mixed
     */
    public function process(Order $order)
    {
        if (!empty($order->admin_id)) {
            return $this->warningResponse('Заказ уже обрабатывает менеджер ' . $order->admin->name);
        }
        if (!$order->status->isNew()) {
            return $this->warningResponse("Заказ находится в статусе \"{$order->status->getLabel()}\", его нельзя взять в работу");
        }

        $order->admin_id = Admin::user()->getAuthIdentifier();
        $order->status = OrderStatus::IN_WORK;
        $order->save();

        return $this->successResponse('Заказ успешно принят в работу');
    }

    /**
     * Generate success response
     *
     * @return mixed
     */
    public function successResponse(string $message)
    {
        if ($this->isRow) {
            return $this->response()->success($message);
        }

        admin_toastr($message, 'success');
    }

    /**
     * Generate warning response
     *
     * @return mixed
     */
    public function warningResponse(string $message)
    {
        if ($this->isRow) {
            return $this->response()->warning($message);
        }

        admin_toastr($message, 'warning');
    }
}
