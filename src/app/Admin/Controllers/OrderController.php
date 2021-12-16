<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Country;
use App\Models\Currency;
use Payments\PaymentMethod;
use App\Models\Orders\Order;
use Deliveries\DeliveryMethod;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Widgets\Table;
use App\Models\Enum\OrderMethod;
use App\Models\Orders\OrderStatus;
use App\Admin\Actions\Order\PrintOrder;
use App\Admin\Actions\Order\ProcessOrder;
use App\Models\Orders\OrderItemStatus;
use Encore\Admin\Auth\Database\Administrator;
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

        $grid->column('id', 'Номер заказа');
        $grid->column('user_full_name', 'ФИО');
        $grid->column('email', __('Email'));
        $grid->column('phone', 'Телефон');

        $grid->model()->with(['items']);
        $grid->column('goods', 'Товары')->expand(function ($model) {
            $items = $model->items->map(function ($item) use ($model) {
                return [
                    'image' => "<img src='{$item->product->getFirstMediaUrl()}' style='width:70px'>",
                    'product' => "<a href='{$item->product->getUrl()}' target='_blank'>{$item->product->getFullName()}</a>",
                    'availability' => $item->product->trashed() ? '<i class="fa fa-close text-red"></i>' : '<i class="fa fa-check text-green"></i>',
                    'status' => $item->status->name_for_admin,
                    'size' => $item->size->name,
                    'price' => "$item->current_price $model->currency",
                ];
            })->toArray();

            return new Table(['Фото', 'Товар', 'Наличие', 'Статус', 'Размер', 'Цена'], $items);
        });
        // $grid->column('comment', 'Коммментарий');
        $grid->column('country.name', 'Страна');
        $grid->column('user_addr', 'Адрес');
        $grid->column('payment.name', 'Способ оплаты');
        $grid->column('delivery.name', 'Способ доставки');

        $grid->column('status_key', 'Статус')->editable('select', OrderStatus::ordered()->pluck('name_for_admin', 'key'));

        if (Admin::user()->inRoles(['administrator', 'director'])) {
            $grid->column('admin_id', 'Менеджер')->editable('select', Administrator::pluck('name', 'id'));
        } else {
            $grid->column('admin.name', 'Менеджер');
        }

        $grid->column('created_at', 'Создан');

        $grid->actions (function ($actions) {
            $actions->add(new ProcessOrder());
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
        $show->panel()->tools($this->getProcessTool($id));

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
        $show->field('country.name', __('Country'));
        $show->field('region', __('Region'));
        $show->field('city', __('City'));
        $show->field('zip', __('Zip'));
        $show->field('user_addr', __('User addr'));

        $show->field('utm_medium', 'utm_medium');
        $show->field('utm_source', 'utm_source');
        $show->field('utm_campaign', 'utm_campaign');
        $show->field('utm_content', 'utm_content');
        $show->field('utm_term', 'utm_term');

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
            $form->tools($this->getProcessTool((int)request('order')));
        }

        $form->text('first_name', 'Имя')->required();;
        $form->text('last_name', 'Фамилия');
        $form->text('patronymic_name', 'Отчество');
        $form->number('user_id', __('User id'));
        $form->number('promocode_id', __('Promocode id'));
        $form->email('email', __('Email'));
        $form->mobile('phone', __('Phone'));
        $form->textarea('comment', __('Comment'));
        $form->select('currency', 'Валюта')->options(Currency::pluck('code', 'code'))
            ->when('BYN', function (Form $form) {
                $form->decimal('rate', 'Курс')->default(Currency::where('code', 'BYN')->value('rate'));
            })->when('KZT', function (Form $form) {
                $form->decimal('rate', 'Курс')->default(Currency::where('code', 'KZT')->value('rate'));
            })->when('RUB', function (Form $form) {
                $form->decimal('rate', 'Курс')->default(Currency::where('code', 'RUB')->value('rate'));
            })->when('USD', function (Form $form) {
                $form->decimal('rate', 'Курс')->default(Currency::where('code', 'USD')->value('rate'));
            })->default('BYN')->required();

        $form->select('country_id', 'Страна')->options(Country::pluck('name', 'id'));
        $form->text('region', __('Region'));
        $form->text('city', __('City'));
        $form->text('zip', __('Zip'));
        $form->text('user_addr', __('User addr'));
        $form->select('delivery_id', 'Способ доставки')->options(DeliveryMethod::pluck('name', 'id'));
        $form->select('payment_id', 'Способ оплаты')->options(PaymentMethod::pluck('name', 'id'));
        $form->select('order_method', 'Способ заказа')
            ->options(OrderMethod::getOptionsForSelect())
            ->default(OrderMethod::DEFAULT);

        $form->hidden('utm_source');
        $form->hidden('utm_medium');
        $form->hidden('utm_campaign');

        $form->select('status_key', 'Статус')->options(OrderStatus::ordered()->pluck('name_for_admin', 'key'));

        if (Admin::user()->inRoles(['administrator', 'director'])) {
            $form->select('admin_id', 'Менеджер')->options(Administrator::pluck('name', 'id'));
        } else {
            $form->display('admin.name', 'Менеджер');
        }

        // Subtable fields
        $form->hasMany('itemsExtended', 'Товары', function (Form\NestedForm $form) {
            $form->display('product_name', 'Название модели');
            $form->image('product_photo', 'Фото товара')->readonly();
            $form->display('size.name', 'Размер');
            $form->select('status_key', 'Статус модели')->options(OrderItemStatus::ordered()->pluck('name_for_admin', 'key'));
            // 'product' => "<a href='{$item->product->getUrl()}' target='_blank'>{$item->product->getFullName()}</a>",
            // 'availability' => $item->product->trashed() ? '<i class="fa fa-close text-red"></i>' : '<i class="fa fa-check text-green"></i>',
            $form->currency('current_price')->symbol($form->getForm()->model()->currency)->readonly();;
        });

        $form->saving(function (Form $form) {
            if (!empty($form->order_method)) {
                list($utmSource, $utmMedium, $utmCampaign) = OrderMethod::getUtmSources($form->order_method);
                $form->utm_source = $utmSource;
                $form->utm_medium = $utmMedium;
                $form->utm_campaign = $utmCampaign;
            }
        });

        return $form;
    }

    /**
     * Handle process order action
     *
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Order $order)
    {
        (new ProcessOrder())->process($order);

        return redirect()->route('admin.orders.edit', $order->id);
    }

    /**
     * Render process tool
     *
     * @param integer $orderId
     * @return \Closure
     */
    protected function getProcessTool(int $orderId)
    {
        return function ($tools) use ($orderId) {
            $tools->append('<div class="btn-group pull-right" style="margin-right: 5px">
                <a  href="' . route('admin.orders.process', $orderId) . '" class="btn btn-sm" style="color: #fff; background-color: #800080; border-color: #730d73;">
                <i class="fa fa-archive"></i>&nbsp;&nbsp;' . (new ProcessOrder)->name . '</a></div>');
        };
    }

    /**
     * Render print tool
     *
     * @return \Closure
     */
    protected function getPrintTool()
    {
        return function ($tools) {
            $tools->append('<div class="btn-group pull-right" style="margin-right: 5px">
                <a onclick="' . PrintOrder::printScript(request('order')) . '" class="btn btn-sm btn-success">
                <i class="fa fa-print"></i>&nbsp;&nbsp;Печать</a></div>');
        };
    }
}
