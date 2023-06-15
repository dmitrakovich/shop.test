<?php

namespace App\Admin\Controllers\Logs;

use App\Admin\Controllers\AbstractAdminController;
use App\Models\Logs\OrderActionLog;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;

class OrderActionController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'История изменения заказов';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderActionLog());

        $grid->column('order_id', 'Id заказа');
        $grid->column('admin.name', 'Менеджер');
        $grid->column('action', 'Действие')->display(fn ($action) => nl2br($action));
        $grid->column('created_at', 'Дата');

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(50);

        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('order_id', 'Id заказа');
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }
}
