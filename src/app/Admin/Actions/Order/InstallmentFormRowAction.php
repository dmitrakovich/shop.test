<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Services\Order\InstallmentOrderService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class InstallmentFormRowAction extends RowAction
{
    public $name = 'Бланк рассрочки';

    /**
     * Action hadle
     *
     * @return Response
     */
    public function handle(Model $model)
    {
        $order = Order::where('id', $model->id)->with([
            'admin',
            'user.passport',
            'onlinePayments',
            'items' => fn ($query) => $query
                ->whereIn('status_key', Order::$itemDepartureStatuses)
                ->with('installment'),
            'user' => fn ($query) => $query->with('lastAddress'),
        ])->first();
        if (!count($order->items)) {
            throw new \Exception('В заказе нет товаров со статусом Забран');
        }
        if (!count($order->items->where('installment.contract_number'))) {
            throw new \Exception('Номер договора рассрочки не заполнен');
        }
        if (!count($order->items->where('installment.num_payments', '>', 0))) {
            throw new \Exception('Количество платежей выбрано некорректно');
        }
        if (!isset($order->user)) {
            throw new \Exception('Привяжите клиента к заказу');
        }
        if (!isset($order->user->passport)) {
            throw new \Exception('Заполните паспортные данные клиента');
        }
        $installmentService = new InstallmentOrderService();
        $file = $installmentService->createInstallmentForm($order);

        return $this->response()->success('Бланк рассрочки успешно создан')->download($file);
    }
}
