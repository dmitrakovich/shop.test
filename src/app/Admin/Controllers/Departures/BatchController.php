<?php

namespace App\Admin\Controllers\Departures;

use App\Models\Orders\Batch;

use App\Admin\Actions\Order\DeleteBatchAction;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Table;

class BatchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Партии';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Batch());
        $grid->model()->with('orders');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Номер партии');
        });

        $grid->column('id', 'Номер партии')->sortable();
        $grid->column('created_at', 'Дата создания')->display(fn ($date) => ($date ? date('d.m.Y H:i:s', strtotime($date)) : null))->sortable();
        $grid->column('dispatch_date', 'Дата отправки')->display(fn ($date) => ($date ? date('d.m.Y H:i:s', strtotime($date)) : null))->sortable();
        $grid->column('orders', 'Кол-во заказов')->display(fn ($orders) => (!empty($orders) ? count($orders) : null));


        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {
            $actions->add(new DeleteBatchAction);
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();
        $grid->paginate(50);
        $grid->disableExport();

        return $grid;
    }
}
