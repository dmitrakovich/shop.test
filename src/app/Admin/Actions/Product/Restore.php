<?php

namespace App\Admin\Actions\Product;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Restore extends RowAction
{
    public $name = 'Восстановить';

    public function handle(Model $model)
    {
        if (!method_exists($model, 'restore')) {
            $model->restore();
        } else {
            throw new \Exception('Модель не подлежит восстановлению');
        }

        return $this->response()->success('Восстановлено')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Вы уверены, что хотите восстановить?');
    }
}
