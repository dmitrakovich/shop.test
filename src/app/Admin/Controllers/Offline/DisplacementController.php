<?php

namespace App\Admin\Controllers\Offline;

use App\Admin\Actions\Offline\DisplacementLabelAction;
use App\Models\Offline\Displacement;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class DisplacementController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Перемещения';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Displacement());
        $grid->model()->with([
            'directionFromStock' => fn ($query) => $query->with('city'),
            'directionToStock' => fn ($query) => $query->with('city'),
        ])->orderBy('id', 'desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Номер партии');
        });

        $grid->column('id', 'Номер партии')->sortable();
        $grid->column('direction', 'Направление')->display(fn () => "{$this->directionFromStock->city->name} ({$this->directionFromStock->name}) - {$this->directionToStock->city->name} ({$this->directionToStock->name})");
        $grid->column('created_at', 'Дата/время создания')->display(fn ($createdAt) => date('d.m.Y H:i:s', strtotime($createdAt)));
        $grid->column('dispatch_date', 'Дата/время отправки')->display(fn ($dispatchDate) => date('d.m.Y H:i:s', strtotime($dispatchDate)));

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->add(new DisplacementLabelAction());
        });
        $grid->paginate(50);
        $grid->disableExport();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Displacement());

        // $stocks = Stock::with('city')
        //     ->get()
        //     ->mapWithKeys(fn ($item) => [$item->id => "{$item->city->name} - {$item->name} - {$item->internal_name}"]);
        $orderItems = OrderItem::where('status_key', 'displacement')
            ->with(['order', 'product'])
            ->get()
            ->mapWithKeys(
                fn ($item) => [$item->id => "{$item->product->shortName()} - Заказ № {$item->order->id} - ФИО: {$item->order->first_name} {$item->order->last_name} {$item->order->patronymic_name}"],
            );

        $form->date('dispatch_date', 'Дата/время отправки')->required();
        $form->multipleSelect('orderItems', 'Товары')->options($orderItems);
        $form->select('direction', 'Направление')->options([
            1 => 'Брест-Минск',
            2 => 'Минск-Брест',
        ]);
        $form->hidden('direction_from');
        $form->hidden('direction_to');
        // $form->select('direction_from', 'Направление из')->options($stocks);
        // $form->select('direction_to', 'Направление в')->options($stocks);

        $form->submitted(function (Form $form) {
            $direction = request()->get('direction');
            $form->direction_from = ($direction == 1) ? 3 : 17;
            $form->direction_to = ($direction == 2) ? 3 : 17;
            $form->ignore('direction');
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
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
