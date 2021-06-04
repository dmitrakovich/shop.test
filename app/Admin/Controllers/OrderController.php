<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;

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

        // $grid->column('id', __('Id'));
        $grid->column('user_name', 'ФИО');
        // $grid->column('user_id', __('User id'));
        // $grid->column('promocode_id', __('Promocode id'));
        $grid->column('type', 'Тип заказа');
        $grid->column('email', __('Email'));
        $grid->column('phone', 'Телефон');
        // $grid->column('currency', __('Currency'));
        // $grid->column('rate', __('Rate'));
        // $grid->column('country', __('Country'));
        // $grid->column('region', __('Region'));
        // $grid->column('city', __('City'));
        // $grid->column('zip', 'Индекс');
        // $grid->column('street', __('Street'));
        // $grid->column('house', __('House'));


        $grid->model()->with(['data']);
        $grid->column('goods', 'Товары')->expand(function ($model) {
            $items = $model->data->map(function ($item) {
                return [
                    'image' => "<img src='{$item->product->getFirstMediaUrl()}' style='width:70px'>",
                    'product' => "<a href='{$item->product->getUrl()}' target='_blank'>{$item->product->getFullName()}</a>",
                    'availability' => $item->product->trashed() ? '<i class="fa fa-close text-red"></i>' : '<i class="fa fa-check text-green"></i>',
                    'size' => $item->size->name,
                    'price' => "$item->price BYN",
                ];
            });
            return new Table(['Фото', 'Товар', 'Наличие', 'Размер', 'Цена'], $items->toArray());
        });
        $grid->column('comment', 'Коммментарий');
        $grid->column('user_addr', 'Адрес');
        $grid->column('payment', 'Способ оплаты');
        // $grid->column('payment_code', __('Payment code'));
        // $grid->column('payment_cost', __('Payment cost'));
        $grid->column('delivery', 'Способ доставки');
        // $grid->column('delivery_code', __('Delivery code'));
        // $grid->column('delivery_cost', __('Delivery cost'));
        // $grid->column('delivery_point', __('Delivery point'));
        // $grid->column('delivery_point_code', __('Delivery point code'));
        // $grid->column('source', __('Source'));
        $grid->column('created_at', 'Создан');
        // $grid->column('updated_at', __('Updated at'));

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
        $show->field('street', __('Street'));
        $show->field('house', __('House'));
        $show->field('user_addr', __('User addr'));
        $show->field('payment', __('Payment'));
        $show->field('payment_code', __('Payment code'));
        $show->field('payment_cost', __('Payment cost'));
        $show->field('delivery', __('Delivery'));
        $show->field('delivery_code', __('Delivery code'));
        $show->field('delivery_cost', __('Delivery cost'));
        $show->field('delivery_point', __('Delivery point'));
        $show->field('delivery_point_code', __('Delivery point code'));
        $show->field('source', __('Source'));
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
        $form->text('street', __('Street'));
        $form->text('house', __('House'));
        $form->text('user_addr', __('User addr'));
        $form->text('payment', __('Payment'));
        $form->text('payment_code', __('Payment code'));
        $form->decimal('payment_cost', __('Payment cost'));
        $form->text('delivery', __('Delivery'));
        $form->text('delivery_code', __('Delivery code'));
        $form->decimal('delivery_cost', __('Delivery cost'));
        $form->text('delivery_point', __('Delivery point'));
        $form->text('delivery_point_code', __('Delivery point code'));
        $form->switch('source', __('Source'));

        return $form;
    }
}
