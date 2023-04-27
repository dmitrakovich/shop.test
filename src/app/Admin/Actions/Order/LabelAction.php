<?php

namespace App\Admin\Actions\Order;

use App\Services\Departures\LabelService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class LabelAction extends RowAction
{
    public $name = 'Этикетка';

    public function handle(Model $model)
    {
        $labelService = new LabelService;
        $file = $labelService->createLabel($model->id);

        return $this->response()->success('Этикетка успешно создана')->download($file);
    }
}
