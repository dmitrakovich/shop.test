<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use App\Admin\Actions\Order\PrintOrder;
use App\Models\Country;
use App\Models\Enum\OrderMethod;
use Deliveries\DeliveryMethod;
use Encore\Admin\Controllers\AdminController;
use Payments\PaymentMethod;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order';

    /**
     * Order methods list
     *
     * @var array
     */
    protected $orderMethods = [
        OrderMethod::DEFAULT => 'через корзину',
        OrderMethod::ONECLICK => 'в один клик',
        OrderMethod::PHONE => 'по телефону',
        OrderMethod::VIBER => 'через viber',
        OrderMethod::INSTAGRAM => 'через instagram',
        OrderMethod::OTHER => 'другое',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->column('user_full_name', 'ФИО');
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
        $show->field('first_name', 'Имя');
        $show->field('last_name', 'Фамилия');
        $show->field('patronymic_name', 'Отчество');
        $show->field('user_id', __('User id'));
        $show->field('promocode_id', __('Promocode id'));
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

        $form->text('first_name', 'Имя');
        $form->text('last_name', 'Фамилия');
        $form->text('patronymic_name', 'Отчество');
        $form->number('user_id', __('User id'));
        $form->number('promocode_id', __('Promocode id'));
        $form->email('email', __('Email'));
        $form->mobile('phone', __('Phone'));
        $form->textarea('comment', __('Comment'));
        $form->text('currency', __('Currency'));
        $form->decimal('rate', __('Rate'));
        $form->select('country_id', 'Страна')->options(Country::pluck('name', 'id'));
        $form->text('region', __('Region'));
        $form->text('city', __('City'));
        $form->text('zip', __('Zip'));
        $form->text('user_addr', __('User addr'));
        $form->select('delivery_id', 'Способ доставки')->options(DeliveryMethod::pluck('name', 'id'));
        $form->select('payment_id', 'Способ оплаты')->options(PaymentMethod::pluck('name', 'id'));
        $form->select('order_method', 'Способ заказа')->options($this->orderMethods);

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
