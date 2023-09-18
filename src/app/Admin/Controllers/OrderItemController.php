<?php

namespace App\Admin\Controllers;

use App\Models\Orders\OrderItemExtended;
use App\Models\Orders\OrderItemStatus;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;

class OrderItemController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'OrderItemExtended';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderItemExtended());

        $grid->column('id', 'orderItemId');
        $grid->column('product_id', 'Код товара');
        $grid->column('product_name', 'Наименование товара');
        $grid->column('size.name', 'Размер');
        $grid->column('order_id', 'Номер заказа')->display(function ($orderId) {
            return "<a href='orders/$orderId/edit' target='_blank'>$orderId</a>";
        });
        $grid->column('status.name_for_admin', 'Статус модели');
        $grid->column('stock_name', 'Склад');
        $grid->column('order.created_at', 'Дата заказа');
        $grid->column('dispatch_date', 'Дата отправки');
        $grid->column('fulfilled_date', 'Дата выкупа');

        $grid->model()->with([
            'product',
            'order.batch',
            'installment',
            'inventoryNotification.stock.media',
        ])->orderBy('id', 'desc');

        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->between('created_at', 'Дата заказа')->datetime();
            $filter->between('order.batch.dispatch_date', 'Дата отправки')->datetime();
            $filter->between('inventoryNotification.completed_at', 'Дата выкупа')->datetime();
            $filter->equal('product_id', 'Код товара');
            $filter->equal('order_id', 'Номер заказа');
            $filter->like('order.phone', 'Номер телефона');
            $filter->equal('status_key', 'Статус товара')->select(OrderItemStatus::pluck('name_for_admin', 'key'));
        });

        // $grid->exporter(new StockExporter());
        // $grid->paginate(100);
        // $grid->perPages([50, 100, 250, 500, 1000]);
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }
}
