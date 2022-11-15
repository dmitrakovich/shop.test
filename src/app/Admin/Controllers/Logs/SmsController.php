<?php

namespace App\Admin\Controllers\Logs;

use App\Models\Logs\SmsLog;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

class SmsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'SmsLog';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SmsLog());

        $grid->column('id', 'id')->hide();
        $grid->column('admin.name', 'Менеджер');
        $grid->column('order_id', 'Заказ')->modal(function ($model) {
            if (empty($model->order_id) || empty($order = $model->order()->first())) {
                return 'Смс сообщение не связанно с заказом';
            }

            return new Table([], [
                'id' => $order->id,
                'ФИО' => $order->user_full_name,
                'email' => $order->email,
                'Номер телефона' => $order->phone,
                'Адрес' => "$order->city, $order->user_addr",
                'Комментарий' => $order->comment,
                'Дата заказа' => $order->created_at,
            ]);
        });
        $grid->column('route', 'Тип (маршрут)');
        $grid->column('phone', 'Номер телефона');
        $grid->column('text', 'Текс сообщения');
        $grid->column('status', 'Статус');
        $grid->column('created_at', 'Дата и время отправки');

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(50);

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return back();
    }
}
