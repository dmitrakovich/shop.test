<?php

namespace App\Admin\Controllers\Departures;

use App\Admin\Actions\Order\DeleteBatchAction;
use App\Admin\Actions\Order\SendBatchAction;
use App\Admin\Selectable\Orders;
use App\Models\Orders\Batch;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

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
            $actions->add(new SendBatchAction);
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->disableCreateButton();
        $grid->paginate(50);
        $grid->disableExport();

        return $grid;
    }

    /**
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Batch());

        $form->belongsToMany('orders', Orders::class, 'Заказы');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
