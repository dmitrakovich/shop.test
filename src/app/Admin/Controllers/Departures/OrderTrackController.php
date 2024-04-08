<?php

namespace App\Admin\Controllers\Departures;

use App\Admin\Actions\Order\TrackRange;
use App\Enums\DeliveryTypeEnum;
use App\Models\Orders\OrderTrack;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * @mixin OrderTrack
 * @phpstan-require-extends OrderTrack
 */
class OrderTrackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Трек номера';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderTrack());
        $grid->model()->with('order.delivery')->orderBy('id', 'desc');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('track_number', 'Трек номер');
            $filter->like('track_link', 'Ссылка для отслеживания трек номера');
            $filter->equal('order_id', 'Номер заказа');
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('track_number', 'Трек номер');
        $grid->column('track_link', 'Ссылка для отслеживания трек номера');
        $grid->column('order_id', 'Номер заказа')->editable();
        $grid->column('delivery_type_enum', 'Тип отправки')->display(fn () => ($this->delivery_type_enum->name()));
        $grid->column('created_at', 'Дата создания')->display(fn ($date) => ($date ? date('d.m.Y H:i:s', strtotime($date)) : null))->sortable();

        $grid->tools(function ($tools) {
            $tools->append(new TrackRange());
        });
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $form = new Form(new OrderTrack());
        $form->text('track_number', 'Трек номер');
        $form->text('track_link', 'Ссылка для отслеживания трек номера');
        $form->text('order_id', 'Номер заказа')->rules('nullable|exists:orders,id');
        $form->select('delivery_type_enum', 'Номер заказа')->options(DeliveryTypeEnum::list())->default(DeliveryTypeEnum::BELPOST->value)->required();

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
