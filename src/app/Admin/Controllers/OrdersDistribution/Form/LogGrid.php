<?php

namespace App\Admin\Controllers\OrdersDistribution\Form;

use App\Models\Logs\OrderDistributionLog;
use Encore\Admin\Grid;

/**
 * @mixin OrderDistributionLog
 */
class LogGrid
{
    public $title = 'Лог';

    /**
     * Build a form here.
     */
    public function index()
    {
        $grid = new Grid(new OrderDistributionLog());
        $grid->model()->orderBy('id', 'desc');

        $grid->column('created_at', 'Дата и время')->display(fn () => date('d.m.Y H:i:s', strtotime($this->created_at)));
        $grid->column('order_id', '№ заказа');
        $grid->column('action', 'Комментарий');
        $grid->column('admin.username', 'Менеджер')->display(fn () => $this->admin ? $this->admin->getShortNameAttribute() : null);

        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableActions();
        $grid->disableColumnSelector();

        return $grid->render();
    }
}
