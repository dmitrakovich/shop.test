<?php

namespace App\Admin\Controllers\Departures;

use App\Admin\Actions\Order\CreateBatchAction;
use App\Admin\Actions\Order\InstallmentFormRowAction;
use App\Admin\Actions\Order\LabelAction;
use App\Admin\Actions\Order\StatusBulkChange;
use App\Admin\Controllers\AbstractAdminController;
use App\Models\Orders\Order;
use App\Models\Orders\OrderStatus;
use App\Services\AdministratorService;
use Deliveries\DeliveryMethod;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class OrderToSendController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Заказы на отправку';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);
        $grid->model()->whereIn('status_key', ['packaging', 'ready', 'sent'])->doesntHave('batch')->orderBy('id', 'desc');

        $admins = (new AdministratorService)->getAdministratorList();
        $orderStatuses = OrderStatus::ordered()->pluck('name_for_admin', 'key');
        $deliveryMethods = DeliveryMethod::pluck('name', 'id');

        $grid->filter(function ($filter) use ($orderStatuses, $admins, $deliveryMethods) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Номер заказа');
            $filter->equal('admin_id', 'Менеджер')->select($admins);
            $filter->equal('status_key', 'Статус')->select($orderStatuses);
            $filter->equal('delivery.id', 'Способ доставки')->select($deliveryMethods);
            $filter->where(function ($query) {
                foreach (explode(' ', $this->input) as $fioPart) {
                    $query->orWhere('first_name', 'like', '%' . $fioPart . '%');
                    $query->orWhere('last_name', 'like', '%' . $fioPart . '%');
                    $query->orWhere('patronymic_name', 'like', '%' . $fioPart . '%');
                }
            }, 'ФИО', 'user_full_name');
            $filter->like('country.name', 'Страна');
            $filter->like('city', 'Город');
        });

        $grid->column('id', 'Номер заказа')->sortable();
        $grid->column('admin_id', 'Менеджер')->editable('select', $admins);
        $grid->column('status_key', 'Статус')->editable('select', $orderStatuses);
        $grid->column('delivery.name', 'Способ доставки');
        $grid->column('user_full_name', 'ФИО');
        $grid->column('country.name', 'Страна');
        $grid->column('city', 'Город');
        $grid->column('user_addr', 'Адрес')->hide();

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new CreateBatchAction);
            $batch->add(new StatusBulkChange);
        });
        $grid->actions(function ($actions) {
            $actions->add(new LabelAction);
            $actions->add(new InstallmentFormRowAction);
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
        return redirect(route('admin.orders.edit', $id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);
        $form->tab('Основное', function ($form) {
            $form->select('status_key', 'Статус')->options(OrderStatus::ordered()->pluck('name_for_admin', 'key'))
                ->default(OrderStatus::DEFAULT_VALUE)->required();
            $form->select('admin_id', 'Менеджер')->options((new AdministratorService)->getAdministratorList());
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
