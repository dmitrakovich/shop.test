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
        $order = Order::where('id', $model->id)->whereHas('user.passport')->exists();
        if (!$order) {
            throw new \Exception('Заполните паспортные данные клиента');
        }
        $installmentService = new InstallmentOrderService;
        $file = $installmentService->createInstallmentForm($model->id);

        return $this->response()->success('Бланк рассрочки успешно создан')->download($file);
    }
}
