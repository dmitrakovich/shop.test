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
            'user.passport',
            'items' => fn ($query) => $query
                ->whereHas('status', fn ($q) => $q->where('key', 'pickup'))
                ->with('installment'),
            'user' => fn ($query) => $query->with('lastAddress')
        ])->first();
        if (!count($order->items)) {
            throw new \Exception('В заказе нет товаров со статусом Забран');
        }
        if (!count($order->items->where('installment.contract_number'))) {
            throw new \Exception('Номер договора рассрочки не заполнен');
        }
        if (!isset($order->user)) {
            throw new \Exception('Привяжите клиента к заказу');
        }
        if (!isset($order->user->passport)) {
            throw new \Exception('Заполните паспортные данные клиента');
        }
        $installmentService = new InstallmentOrderService;
        $file = $installmentService->createInstallmentForm($order);

        return $this->response()->success('Бланк рассрочки успешно создан')->download($file);
    }
}
