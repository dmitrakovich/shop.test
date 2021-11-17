<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use App\Admin\Actions\Order\PrintOrder;
use Encore\Admin\Controllers\AdminController;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->column('user_name', 'ФИО');
        $grid->column('type', 'Тип заказа');
        $grid->column('email', __('Email'));
        $grid->column('phone', 'Телефон');

        $grid->model()->with(['data']);
        $grid->column('goods', 'Товары')->expand(function ($model) {
            $items = $model->data->map(function ($item) use ($model) {
                return [
                    'image' => "<img src='{$item->product->getFirstMediaUrl()}' style='width:70px'>",
                    'product' => "<a href='{$item->product->getUrl()}' target='_blank'>{$item->product->getFullName()}</a>",
                    'availability' => $item->product->trashed() ? '<i class="fa fa-close text-red"></i>' : '<i class="fa fa-check text-green"></i>',
                    'size' => $item->size->name,
                    'price' => "$item->current_price $model->currency",
                ];
            });
            return new Table(['Фото', 'Товар', 'Наличие', 'Размер', 'Цена'], $items->toArray());
        });
        // $grid->column('comment', 'Коммментарий');
        $grid->column('country.name', 'Страна');
        $grid->column('user_addr', 'Адрес');
        $grid->column('payment.name', 'Способ оплаты');
        $grid->column('delivery.name', 'Способ доставки');
        $grid->column('created_at', 'Создан');

        $grid->actions (function ($actions) {
            $actions->add(new PrintOrder());
        });

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(15);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Order::findOrFail($id));

        $show->panel()->tools($this->getPrintTool());

        $show->field('id', __('Id'));
        $show->field('user_name', __('User name'));
        $show->field('user_id', __('User id'));
        $show->field('promocode_id', __('Promocode id'));
        $show->field('type', __('Type'));
        $show->field('email', __('Email'));
        $show->field('phone', __('Phone'));
        $show->field('comment', __('Comment'));
        $show->field('currency', __('Currency'));
        $show->field('rate', __('Rate'));
        $show->field('country', __('Country'));
        $show->field('region', __('Region'));
        $show->field('city', __('City'));
        $show->field('zip', __('Zip'));
        $show->field('user_addr', __('User addr'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        if ($form->isEditing()) {
            $form->tools($this->getPrintTool());
        }

        $form->text('user_name', __('User name'));
        $form->number('user_id', __('User id'));
        $form->number('promocode_id', __('Promocode id'));
        $form->text('type', __('Type'));
        $form->email('email', __('Email'));
        $form->mobile('phone', __('Phone'));
        $form->textarea('comment', __('Comment'));
        $form->text('currency', __('Currency'));
        $form->decimal('rate', __('Rate'));
        $form->text('country', __('Country'));
        $form->text('region', __('Region'));
        $form->text('city', __('City'));
        $form->text('zip', __('Zip'));
        $form->text('user_addr', __('User addr'));

        return $form;
    }

    protected function getPrintTool()
    {
        return function ($tools) {
            $tools->append('<div class="btn-group pull-right" style="margin-right: 5px">
                <a onclick="' . PrintOrder::printScript(request('order')) . '" class="btn btn-sm btn-success">
                <i class="fa fa-print"></i>&nbsp;&nbsp;Печать</a></div>');
        };
    }
}
