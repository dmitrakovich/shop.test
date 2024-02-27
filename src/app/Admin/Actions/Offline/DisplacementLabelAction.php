<?php

namespace App\Admin\Actions\Offline;

use App\Services\Departures\BelpostLabelService;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class DisplacementLabelAction extends RowAction
{
    public $name = 'Этикетка (Белпочта)';

    public function handle(Model $model)
    {
        $labelService = app(BelpostLabelService::class);
        $file = $labelService->createDisplacementLabel($model);

        return $this->response()->success('Этикетка успешно создана')->download($file);
    }
}
