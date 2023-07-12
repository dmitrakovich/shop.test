<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Order\BuyoutFormAction;
use App\Admin\Actions\Order\CancelPayment;
use App\Admin\Actions\Order\CapturePayment;
use App\Admin\Actions\Order\CreateOnlinePayment;
use App\Admin\Actions\Order\InstallmentForm;
use App\Admin\Actions\Order\PrintOrder;
use App\Admin\Actions\Order\ProcessOrder;
use App\Admin\Requests\ChangeUserByPhoneRequest;
use App\Events\OrderCreated;
use App\Facades\Currency as CurrencyFacade;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Enum\OrderMethod;
use App\Models\Logs\OrderActionLog;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItemExtended;
use App\Models\Orders\OrderItemStatus;
use App\Models\Orders\OrderStatus;
use App\Models\Payments\Installment;
use App\Models\Payments\OnlinePayment;
use App\Models\Product;
use App\Models\Size;
use App\Models\User\User;
use Deliveries\DeliveryMethod;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Displayers\ContextMenuActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Payments\PaymentMethod;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Заказы';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $orderStatuses = OrderStatus::ordered()->pluck('name_for_admin', 'key');
        $admins = Administrator::pluck('name', 'id');

        $grid->column('id', 'Номер заказа');
        $grid->column('user_full_name', 'ФИО');
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
        $grid->column('country.name', 'Страна');
        $grid->column('city', 'Город');
        $grid->column('user_addr', 'Адрес');
        $grid->column('payment.name', 'Способ оплаты');
        $grid->column('delivery.name', 'Способ доставки');

        $grid->column('status_key', 'Статус')->editable('select', $orderStatuses);
        $grid->column('admin_id', 'Менеджер')->editable('select', $admins);
        $grid->column('created_at', 'Создан');

        $grid->actions(function ($actions) {
            $actions->add(new ProcessOrder());
            $actions->add(new PrintOrder());
        });

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(15);

        $grid->filter(function ($filter) use ($orderStatuses, $admins) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Номер заказа');
            $filter->like('last_name', 'Фамилия');
            $filter->equal('status_key', 'Статус')->select($orderStatuses);
            $filter->equal('admin_id', 'Менеджер')->select($admins);
            $filter->between('created_at', 'Дата заказа')->datetime();
            $filter->equal('order_method', 'Способ заказа')->select(OrderMethod::getOptionsForSelect());
        });

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
        $show->field('comment', 'Коммментарий');
        $show->field('currency', __('Currency'));
        $show->field('rate', __('Rate'));
        $show->field('country.name', __('Country'));
        $show->field('region', __('Region'));
        $show->field('city', 'Город');
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
     * Edit interface.
     *
     * @param  mixed  $id
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->form($id)->edit($id));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(?int $id = null)
    {
        $form = new Form(new Order());
        $order = $id ? Order::where('id', $id)->with(['user'])->first() : null;

        if ($form->isEditing()) {
            $form->tools($this->getPrintTool());
            $form->tools($this->getProcessTool((int)request('order')));
            $form->tools(function (Form\Tools $tools) {
                $tools->append(new BuyoutFormAction((int)request('order')));
                $tools->append(new InstallmentForm((int)request('order')));
            });
        }

        $form->tab('Основное', function ($form) use ($order) {
            $form->text('last_name', 'Фамилия');
            $form->text('first_name', 'Имя')->required();
            $form->text('patronymic_name', 'Отчество');

            $form->hidden('user_id');
            $form->html(view('admin.order.order-client', [
                'order' => $order,
            ]), 'Клиент');

            $form->number('promocode_id', __('Promocode id'));
            $form->email('email', __('Email'));
            $form->phone('phone', 'Телефон')->required();
            $form->textarea('comment', 'Коммментарий');
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
            $form->text('city', 'Город');
            $form->text('zip', __('Zip'));
            $form->text('user_addr', __('User addr'));
            $form->select('delivery_id', 'Способ доставки')->options(DeliveryMethod::pluck('name', 'id'));
            $form->text('track.track_number', 'Трек номер');
            $form->url('track.track_link', 'Ссылка на трек номер');
            $form->currency('weight', 'Вес заказа')->symbol('Кг');
            $form->currency('delivery_cost', 'Стоимость доставки фактическая')->symbol('BYN');
            $form->currency('delivery_price', 'Стоимость доставки для клиента')->symbol('BYN');
            $form->select('payment_id', 'Способ оплаты')->options(PaymentMethod::pluck('name', 'id'));
            $form->select('order_method', 'Способ заказа')
                ->options(OrderMethod::getOptionsForSelect())
                ->default(OrderMethod::DEFAULT);

            $this->setUtmSources($form);

            $form->select('status_key', 'Статус')->options(OrderStatus::ordered()->pluck('name_for_admin', 'key'))
                ->default(OrderStatus::DEFAULT_VALUE)->required();
            $form->select('admin_id', 'Менеджер')->options(Administrator::pluck('name', 'id'));
            $form->hasMany('adminComments', 'Комментарии менеджера', function (Form\NestedForm $form) {
                $form->textarea('comment', 'Комментарий')->rules(['required', 'max:500']);
                $form->display('created_at', 'Дата');
            });
        });

        $form->tab('Товары', function ($form) {
            $form->hasMany('itemsExtended', 'Товары', function (Form\NestedForm $nestedForm) {
                $nestedForm->hidden('id')->addElementClass('order-item-id');
                $currencyCode = $nestedForm->getForm()->model()->currency;
                $nestedForm->select('product_id', 'Код товара')
                    ->options(function ($id) {
                        return [$id => $id];
                    })
                    ->ajax('/api/product/product');
                $nestedForm->hidden('count')->default(1);
                $nestedForm->hidden('buy_price')->default(0);
                $nestedForm->hidden('price');
                $nestedForm->display('product_link', 'Название модели');
                $nestedForm->image('product_photo', 'Фото товара')->readonly();
                $nestedForm->select('size_id', 'Размер')->options(function ($id) {
                    if ($size = Size::find($id)) {
                        return [$size->id => $size->name];
                    }
                })->required();
                $nestedForm->select('status_key', 'Статус модели')
                    ->options(OrderItemStatus::ordered()->pluck('name_for_admin', 'key'))
                    ->default(OrderItemStatus::DEFAULT_VALUE)
                    ->required();
                $nestedForm->currency('old_price', 'Старая цена')->symbol($currencyCode);
                $nestedForm->currency('current_price', 'Стоимость')->symbol($currencyCode);
                $nestedForm->currency('discount', 'Скидка')->symbol('%');

                // installment
                $nestedForm->number('installment_contract_number', 'Номер договора рассрочки')
                    ->addElementClass(['installment-field']);
                $nestedForm->currency('installment_monthly_fee', 'Ежемесячный платёж')
                    ->symbol($currencyCode)
                    ->addElementClass(['installment-field']);
                $nestedForm->switch('installment_send_notifications', 'Отправлять оповещение')
                    ->default(false)
                    ->addElementClass(['installment-field']);
            })->setScript($this->getScriptForExtendedItems());
        });

        if ($id) {
            $form->tab('Платежи', function ($form) use ($id) {
                $form->row(function ($form) use ($id) {
                    $form->html($this->onlinePaymentGrid($id));
                });
            });

            $form->tab('История', function (Form $form) use ($id) {
                $form->row(function ($form) use ($id) {
                    $form->html($this->orderHistoryTable($id));
                });
            });
        }

        $form->saving(function (Form $form) {
            CurrencyFacade::setCurrentCurrency($form->input('currency'), false);
            foreach ($form->itemsExtended ?? [] as $key => $item) {
                if (str_starts_with($key, 'new')) {
                    $product = Product::findOrFail($item['product_id']);
                    $form->input("itemsExtended.$key.price", $product->getPrice());
                    $form->input("itemsExtended.$key.old_price", $product->getOldPrice());
                    $form->input("itemsExtended.$key.current_price", $product->getPrice());
                }
                if ($form->status_key === 'canceled') {
                    $form->input("itemsExtended.$key.status_key", 'canceled');
                }
            }
            if ($form->isCreating()) {
                $form->admin_id = Admin::user()->id;
            }
        });

        $form->saved(function (Form $form) {
            if ((int)$form->input('payment_id') === Installment::PAYMENT_METHOD_ID) {
                $this->saveInstallments($form);
            }
            if ($form->isCreating()) {
                event(new OrderCreated($form->model()));
            }
            // TODO: recalc order total price
        });

        return $form;
    }

    /**
     * Save installments for order items
     */
    protected function saveInstallments(Form $form): void
    {
        /** @var OrderItemExtended $itemExtended */
        foreach ($form->model()->itemsExtended as $itemExtended) {
            $contractNumber = (int)$form->input("itemsExtended.{$itemExtended->id}.installment_contract_number");
            if (!$contractNumber) {
                continue;
            }
            $monthlyFee = (float)$form->input("itemsExtended.{$itemExtended->id}.installment_monthly_fee");
            $sendNotifications = $form->input("itemsExtended.{$itemExtended->id}.installment_send_notifications") === 'on';
            /** @var Installment $installment */
            $installment = $itemExtended->installment()->firstOrNew();
            $installment->contract_number = $contractNumber;
            $installment->monthly_fee = $monthlyFee;
            $installment->send_notifications = $sendNotifications;
            $installment->save();
        }
    }

    private function onlinePaymentGrid($orderId)
    {
        $grid = new Grid(new OnlinePayment());
        $grid->model()->where('order_id', $orderId)->orderBy('id', 'desc');

        $grid->column('created_at', 'Дата/время создания')->display(function ($date) {
            return $date ? date('d.m.Y H:i:s', strtotime($date)) : null;
        })->width(100);
        $grid->column('last_status_enum_id', 'Статус')->display(fn () => $this->last_status_enum_id->name());
        $grid->column('admin.name', 'Менеджер');
        $grid->column('method_enum_id', 'Способ оплаты')->display(fn () => $this->method_enum_id->name());
        $grid->column('amount', 'Сумма платежа');
        $grid->column('paid_amount', 'Сумма оплаченная клиентом');
        $grid->column('currency_code', 'Код валюты');

        $grid->column('expires_at', 'Срок действия платежа')->display(function ($date) {
            return $date ? date('d.m.Y H:i:s', strtotime($date)) : null;
        });
        $grid->column('link', 'Срок действия платежа')->display(function ($link) {
            return '<a href="' . $link . '" target="_blank">Ссылка на станицу оплаты</a>';
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();

            if ($actions->row->canCapturePayment()) {
                $actions->add(new CapturePayment($actions->row));
            }
            if ($actions->row->canCancelPayment()) {
                $actions->add(new CancelPayment($actions->row));
            }
        });
        $grid->tools(function (Grid\Tools $tools) use ($orderId) {
            $tools->append(new CreateOnlinePayment($orderId));
        });
        $grid->setActionClass(ContextMenuActions::class);
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid->render();
    }

    /**
     * Render order histoty table
     */
    private function orderHistoryTable(int $orderId): string
    {
        $headers = ['Id заказа', 'Менеджер', 'Действие', 'Дата'];
        $rows = OrderActionLog::query()
            ->where('order_id', $orderId)
            ->orderBy('id', 'desc')
            ->with('admin:id,name')
            ->get(['order_id', 'admin_id', 'action', 'created_at'])
            ->map(fn (OrderActionLog $log) => [
                $log->order_id,
                $log->admin->name ?? 'SYSTEM',
                nl2br($log->action),
                $log->created_at,
            ])
            ->toArray();

        return (new Table($headers, $rows))->render();
    }

    /**
     * Set utm sources in form
     */
    protected function setUtmSources(Form $form): void
    {
        $form->hidden('utm_source');
        $form->hidden('utm_medium');
        $form->hidden('utm_campaign');

        $form->saving(function (Form $form) {
            if (!empty($form->order_method)) {
                [$utmSource, $utmMedium, $utmCampaign] = OrderMethod::getUtmSources($form->order_method);
                $form->utm_source = $utmSource;
                $form->utm_medium = $utmMedium;
                $form->utm_campaign = $utmCampaign;
            }
        });
    }

    /**
     * Handle process order action
     *
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

    /**
     * Js crutch
     */
    protected function getScriptForExtendedItems(): string
    {
        $installmentPaymentId = Installment::PAYMENT_METHOD_ID;

        return <<<JS
$(function () {
    // disable editing for current items in order
    $('select.product_id').attr('disabled', true);

    // prepare current images
    $('#has-many-itemsExtended .file-input').each(function (index, element) {
        let img = $(element).find('img').first().height(105);
        $(this).empty().append(img);
    });

    // get product data for new item in order
    $(document).on('change', '.itemsExtended.product_id', function () {
        const itemBlock = $(this).parents('.has-many-itemsExtended-form');
        const sizesSelectElement = itemBlock.find('select.size_id');
        const payload = {
            productId: $(this).val(),
            orderItemId: itemBlock.find('.order-item-id').val()
        };
        $.get('/api/product/data', payload, function (response) {
            // console.log(response);
            const img = $('<img>').attr('src', response.image).height(105);
            const link = $('<a>', {
                text: response.name,
                href: response.link,
                target: '_blank',
            });
            $(itemBlock).find('.file-input').empty().append(img);
            $(itemBlock).find('.box.box-solid.box-default .box-body').html(link);
            // sizes
            sizesSelectElement.find('option').remove();
            $(sizesSelectElement).select2({
                placeholder: 'Выбрать',
                allowClear: true,
                data: response.sizes
            });
            if (sizesSelectElement.data('value')) {
                $(sizesSelectElement).val(sizesSelectElement.data('value'));
            }
            $(sizesSelectElement).trigger('change');
        });
    });

    $(document).on('change', '.payment_id', function () {
        $('.installment-field').parents('.form-group').removeClass('hide');
        if ($(this).val() != $installmentPaymentId) {
            $('.installment-field').parents('.form-group').addClass('hide');
        }
    });

    setTimeout(() => {
        // prepare sizes
        $('.itemsExtended.product_id').each(function (index, element) {
            $(element).change();
        });
        // prepare installment fields
        $('.payment_id').change();
    }, 300);
});
JS;
    }

    public function changeUserByPhone(ChangeUserByPhoneRequest $request)
    {
        $user = User::where('phone', $request->input('phone'))->first();
        Order::where('id', $request->input('orderId'))->update(['user_id' => $user->id]);

        return $user;
    }

    public function addUserByPhone(Request $request)
    {
        $user = User::where('phone', $request->input('userCreatePhone'))->first();
        if (!$user) {
            $user = User::create([
                'phone' => $request->input('userCreatePhone'),
                'last_name' => $request->input('userCreateLastName'),
                'first_name' => $request->input('userCreateFirstName'),
                'patronymic_name' => $request->input('userCreatePatronymicName'),
            ]);
        }
        Order::where('id', $request->input('orderId'))->update(['user_id' => $user->id]);

        return $user;
    }
}
