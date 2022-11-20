<?php

namespace App\Admin\Actions\Post;

use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class BatchRestore extends BatchAction
{
    public $name = 'Восстановить';

    public function handle(Collection $collection)
    {
        $collection->each->restore();

        return $this->response()->success('Восстановлены')->refresh();
    }

    public function dialog()
    {
        $this->confirm('Вы уверены, что хотите восстановить?');
    }
}
