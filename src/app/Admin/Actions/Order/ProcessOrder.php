<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;

class ProcessOrder extends RowAction
{
    public $name = 'Взять в работу';

    protected $isRow = false;

    public function handle(Model $model)
    {
        $this->isRow = true;

        return $this->process($model);
    }

    /**
     * Handle action
     *
     * @return mixed
     */
    public function process(Model $model)
    {
        if (!empty($model->admin_id)) {
            return $this->warningResponse('Заказ уже обрабатывает менеджер ' . $model->admin->name);
        }
        if ($model->status_key != 'new') {
            return $this->warningResponse("Заказ находится в статусе \"{$model->status->name_for_admin}\", его нельзя взять в работу");
        }

        $model->admin_id = Admin::user()->id;
        $model->status_key = 'in_work';
        $model->save();

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
