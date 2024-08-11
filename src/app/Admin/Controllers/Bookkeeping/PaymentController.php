<?php

namespace App\Admin\Controllers\Bookkeeping;

use App\Admin\Actions\Order\BelpostImportCODAction;
use App\Admin\Controllers\AbstractAdminController;
use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Enums\Payment\OnlinePaymentStatusEnum;
use App\Models\Orders\Order;
use App\Models\Payments\OnlinePayment;
use App\Services\AdministratorService;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;

/**
 * @mixin OnlinePayment
 */
class PaymentController extends AbstractAdminController
{
    protected $title = 'Платежи';

    protected function grid()
    {
        $grid = new Grid(new OnlinePayment());
        $grid->model()->orderBy('id', 'desc');
        $adminUsers = (new AdministratorService())->getAdministratorList();

        $grid->filter(function ($filter) use ($adminUsers) {
            $filter->like('payment_num', 'Номер счета');
            $filter->like('payment_id', 'Billnumber');
            $filter->like('order_id', 'Номер заказа');
            $filter->in('method_enum_id', 'Тип платежа')->multipleSelect(OnlinePaymentMethodEnum::list());
            $filter->in('admin_user_id', 'Менеджер')->multipleSelect($adminUsers);
            $filter->like('fio', 'ФИО клиента');
            $filter->between('created_at', 'Дата создания')->datetime();
            $filter->between('lastCanceledStatus.created_at', 'Дата отмены')->datetime();
            $filter->between('lastSucceededStatus.created_at', 'Дата оплаты')->datetime();
            $filter->like('amount', 'Выставленная сумма');
            $filter->like('paid_amount', 'Оплаченная сумма');
            $filter->where(function ($query) {
                $query->whereHas('lastStatus', function ($q) {
                    $q->where('payment_status_enum_id', $this->input);
                });
            }, 'Статус заказа')->multipleSelect(OnlinePaymentStatusEnum::list());
            $filter->disableIdFilter();
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('payment_num', 'Номер счета');
        $grid->column('payment_id', 'Billnumber');
        $grid->column('order_id', 'Номер заказа')->display(function ($order_id) {
            return '<a href="' . route(config('admin.route.prefix') . '.orders.edit', $order_id) . '" target="_blank" > ' . $order_id . '</a>';
        });
        $grid->column('method_enum_id', 'Тип платежа')->display(fn ($method_enum_id) => OnlinePaymentMethodEnum::tryFrom($method_enum_id)->name());
        $grid->column('admin.name', 'Менеджер')->display(fn () => $this->admin?->short_name);
        $grid->column('fio', 'ФИО клиента')->hide();
        $grid->column('created_at', 'Дата создания')->display(fn ($created_at) => date('d.m.Y H:i:s', strtotime($created_at)))->sortable();
        $grid->column('lastCanceledStatus.created_at', 'Дата отмены')->display(fn ($created_at) => $created_at ? date('d.m.Y H:i:s', strtotime($created_at)) : null);
        $grid->column('lastSucceededStatus.created_at', 'Дата оплаты')->display(fn ($created_at) => $created_at ? date('d.m.Y H:i:s', strtotime($created_at)) : null);
        $grid->column('amount', 'Выставленная сумма');
        $grid->column('paid_amount', 'Оплаченная сумма');
        $grid->column('lastStatus.payment_status_enum_id', 'Статус')->display(function ($last_status_enum_id) {
            $enum = OnlinePaymentStatusEnum::tryFrom($last_status_enum_id);

            return $enum ? $enum->name() : null;
        });
        $grid->column('comment', 'Комментарий');

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new BelpostImportCODAction());
        });
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        $grid->paginate(50);
        $grid->perPages([25, 50, 100, 500]);
        $grid->disableExport();
        $grid->disableRowSelector();

        return $grid;
    }

    protected function form()
    {
        $form = new Form(new OnlinePayment());
        $form->select('method_enum_id', 'Тип платежа')->options(OnlinePaymentMethodEnum::list())->placeholder('Введите сумму платежа')->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
        $form->date('lastSucceededStatus.created_at', 'Дата оплаты');
        $form->hidden('lastSucceededStatus.admin_user_id')->value(Admin::user()->id ?? null);
        $form->hidden('lastSucceededStatus.payment_status_enum_id')->value(OnlinePaymentStatusEnum::SUCCEEDED->value);
        $form->text('amount', 'Сумма платежа')->placeholder('Введите сумму платежа')->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
        $form->text('payment_id', 'Billnumber')->placeholder('Введите Billnumber')->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
        $form->text('order_id', 'Номер заказа')->rules('required|exists:orders,id', [
            'exists' => 'Заказ с переданным id не найден',
        ])->placeholder('Введите номер заказа');
        $form->textarea('comment', 'Комментарий');
        $form->hidden('fio');
        $form->hidden('email');
        $form->hidden('phone');
        $form->hidden('link_code');

        $form->saving(function (Form $form) {
            $orderId = $form->order_id;
            $order = Order::find($orderId);
            $form->fio = $order->user_full_name ?? null;
            $form->email = $order->email ?? null;
            $form->phone = $order->phone ?? null;
            $form->link_code = uniqid();
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
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
