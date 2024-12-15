<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Order\BuyoutFormAction;
use App\Admin\Actions\Order\CancelPayment;
use App\Admin\Actions\Order\CapturePayment;
use App\Admin\Actions\Order\CreateOnlinePayment;
use App\Admin\Actions\Order\DistributeOrderAction;
use App\Admin\Actions\Order\EnvelopeAction;
use App\Admin\Actions\Order\InstallmentForm;
use App\Admin\Actions\Order\PrintOrder;
use App\Admin\Actions\Order\ProcessOrder;
use App\Admin\Requests\ChangeUserByPhoneRequest;
use App\Admin\Requests\UserAddressRequest;
use App\Enums\Order\OrderTypeEnum;
use App\Enums\Order\UtmEnum;
use App\Enums\StockTypeEnum;
use App\Events\Analytics\OfflinePurchase;
use App\Events\OrderCreated;
use App\Facades\Currency as CurrencyFacade;
use App\Models\Currency;
use App\Models\Enum\OrderMethod;
use App\Models\Logs\OrderActionLog;
use App\Models\Orders\Order;
use App\Models\Orders\OrderAdminComment;
use App\Models\Orders\OrderItemExtended;
use App\Models\Orders\OrderItemStatus;
use App\Models\Orders\OrderStatus;
use App\Models\Payments\Installment;
use App\Models\Payments\OnlinePayment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User\User;
use App\Services\AdministratorService;
use App\Services\Order\OrderItemInventoryService;
use Deliveries\DeliveryMethod;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Displayers\ContextMenuActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Payments\PaymentMethod;

/**
 * @mixin Order
 * @mixin OnlinePayment
 */
class OrderController extends AbstractAdminController
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
        $admins = app(AdministratorService::class)->getAdministratorList();

        $grid->column('id', 'Номер заказа');
        $grid->column('user_full_name', 'ФИО');
        $grid->column('phone', 'Телефон');

        $grid->model()->with(['items', 'adminComments']);
        $grid->column('goods', 'Товары')->expand(function ($model) {
            $items = $model->items->map(function ($item) use ($model) {
                return [
                    'image' => "<img src='{$item->product->getFirstMediaUrl()}' style='width:70px'>",
                    'product' => "<a href='{$item->product->getUrl()}' target='_blank'>{$item->product->getFullName()}</a>",
                    'availability' => $item->product->trashed() ? '<i class="fa fa-close text-red"></i>' : '<i class="fa fa-check text-green"></i>',
                    'status' => $item->status->name_for_admin,
                    'size' => $item->size?->name,
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
        $grid->column('adminCommentsCollection', 'Коммент')
            ->display(fn () => count($this->adminComments) ? '💬' : null)
            ->display(fn ($value, $row) => count($this->adminComments) ? $row->expand(function ($model) {
                $comments = $model->adminComments->map(function ($comment) {
                    return $comment->only(['created_at', 'comment']);
                });

                return new Table(['Дата создания', 'Коммент'], $comments->toArray());
            }) : null);

        $grid->column('status_key', 'Статус')->editable('select', $orderStatuses);
        $grid->column('admin_id', 'Менеджер')->editable('select', $admins);
        $grid->column('created_at', 'Создан');

        $grid->actions(function ($actions) {
            $actions->add(new ProcessOrder());
            $actions->add(new PrintOrder());
        });

        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new DistributeOrderAction());
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
        $order = $id ? Order::query()->where('id', $id)->with([
            'country',
            'user' => fn ($query) => $query->with([
                'blacklist',
                'lastAddress.country',
            ]),
        ])->first() : null;

        $administratorService = app(AdministratorService::class);
        $adminList = $administratorService->getAdministratorList();
        $adminLoginList = $administratorService->getAdministratorLoginList();

        if ($form->isCreating()) {
            $form->hidden('order_type')->value(OrderTypeEnum::MANAGER);
        }
        if ($form->isEditing()) {
            $form->tools($this->getPrintTool());
            $form->tools($this->getProcessTool((int)request('order')));
            $form->tools(function (Form\Tools $tools) {
                $tools->append(new BuyoutFormAction((int)request('order')));
                $tools->append(new EnvelopeAction((int)request('order')));
                $tools->append(new InstallmentForm((int)request('order')));
            });
        }

        $form->tab('Основное', function ($form) use ($adminList, $adminLoginList, $order) {
            if ($form->isCreating()) {
                $form->select('order_method', 'Способ заказа')
                    ->options(OrderMethod::getOptionsForSelect())
                    ->default(OrderMethod::UNDEFINED);
            } elseif ($order) {
                $utmEnum = UtmEnum::tryFrom("{$order->utm_source}-{$order->utm_campaign}");
                if (!$order->utm_source || ($order->utm_source == 'none')) {
                    $orderSource = 'Неизвестен';
                } else {
                    $utmContent = $order->utm_content ? mb_strtolower($order->utm_content) : null;
                    $managerName = ($order->utm_campaign === 'manager' && $utmContent) ? ($adminLoginList[$utmContent] ?? '') : '';
                    $orderSource = $utmEnum ? $utmEnum->channelName() . ' ' . $utmEnum->companyName() : trim("{$order->utm_source} {$order->utm_campaign} $managerName");
                }
                $orderType = $order->order_type?->name();
                $form->html(
                    '<h5>' . ($orderType ? "{$orderType} - " : '') . "{$orderSource}</h5>",
                    'Тип / источник заказа'
                );
            }

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
            $form->textarea('comment', 'Комментарий (виден клиенту!)');
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

            if ($order) {
                $form->html(view('admin.order.order-address', [
                    'order' => $order,
                ]), 'Адреса');
            }

            $form->select('delivery_id', 'Способ доставки')->options(DeliveryMethod::query()->pluck('name', 'id'));
            $form->select('stock_id', 'Адрес ПВЗ для выдачи')->options(Stock::query()->where('type', StockTypeEnum::SHOP)->pluck('address', 'id'));
            $form->text('track.track_number', 'Трек номер');
            $form->url('track.track_link', 'Ссылка на трек номер');
            $form->currency('weight', 'Вес заказа')->symbol('Кг');
            $form->currency('delivery_cost', 'Стоимость доставки фактическая')->symbol('BYN');
            $form->currency('delivery_price', 'Стоимость доставки для клиента')->symbol('BYN');
            $form->select('payment_id', 'Способ оплаты')
                ->options(PaymentMethod::query()->pluck('name', 'id'))
                ->when(Installment::PAYMENT_METHOD_ID, function (Form $form) {
                    $form->date('installment_contract_date', 'Дата договора');
                });

            $this->setUtmSources($form);

            $form->select('status_key', 'Статус')->options(OrderStatus::ordered()->pluck('name_for_admin', 'key'))
                ->default(OrderStatus::DEFAULT_VALUE)->required();
            $form->select('admin_id', 'Менеджер')->options($adminList);
        });

        $form->tab('Товары', function (Form $form) {
            $form->hasMany('itemsExtended', 'Товары', function (Form\NestedForm $nestedForm) {
                /** @var OrderItemExtended */
                $orderItem = $nestedForm->model();
                $currencyCode = $nestedForm->getForm()->model()->currency;
                $nestedForm->hidden('id')->addElementClass('order-item-id');
                $nestedForm->select('product_id', 'Код товара')
                    ->options(function ($id) {
                        return [$id => $id];
                    })
                    ->ajax('/api/admin/product/product');
                $nestedForm->hidden('count')->default(1);
                $nestedForm->hidden('buy_price')->default(0);
                $nestedForm->hidden('price');
                $nestedForm->display('product_link', 'Название модели');
                $nestedForm->select('stock_id', 'Склад')
                    ->options(['загрузка...'])
                    ->addElementClass($orderItem?->status_key === 'new' ? [] : ['disabled'])
                    ->required();
                $nestedForm->image('product_photo', 'Фото товара')->readonly();
                $nestedForm->select('size_id', 'Размер')
                    ->options(['загрузка...'])
                    ->addElementClass($orderItem?->isFinalStatus() ? ['disabled'] : [])
                    ->required();
                $nestedForm->select('item_status_key', 'Статус модели')
                    ->options(OrderItemStatus::ordered()->pluck('name_for_admin', 'key'))
                    ->addElementClass($orderItem?->isFinalStatus() ? ['disabled'] : [])
                    ->default(OrderItemStatus::DEFAULT_VALUE)
                    ->required();
                $nestedForm->currency('old_price', 'Старая цена')->symbol($currencyCode);
                $nestedForm->currency('current_price', 'Стоимость')->symbol($currencyCode);
                $nestedForm->currency('discount', 'Скидка')->symbol('%');

                // installment
                $nestedForm->select('installment_num_payments', 'Количество платежей')
                    ->options([
                        0 => 'Без рассрочки',
                        2 => '2 платежа',
                        3 => '3 платежа',
                    ])
                    ->default(0)
                    ->addElementClass(['installment-field']);
                $nestedForm->text('installment_contract_number', 'Номер договора рассрочки')
                    ->placeholder('Номер заказа / номер позиции заказа. При создании оставить пустым!')
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
            $form->tab('Комментарии менеджера', function (Form $form) use ($id) {
                $form->row(function ($form) use ($id) {
                    $form->html($this->adminCommentsGrid($id));
                    $form->html(view('admin.order.order-comment', [
                        'orderId' => $id,
                    ]), 'Комментарии');
                });
            });

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

        $form->submitted(function (Form $form) {
            $orderTrack = request()->input('track');
            $orderTrackNumber = $orderTrack['track_number'] ?? null;
            $orderTrackLink = $orderTrack['track_link'] ?? null;
            if (!$orderTrackNumber && !$orderTrackLink) {
                $form->ignore('track');
            }
            $orderItems = array_filter(request()->input('itemsExtended') ?? [], fn (array $item) => !$item['_remove_']);
            if (empty($orderItems) && request()->pjax()) {
                return $this->emptyItemsError();
            }
            $statusKey = request()->input('status_key');
            if ($statusKey === 'packaging') {
                $addressApprove = Order::query()
                    ->where('id', $form->model()->id)
                    ->whereHas('user', fn ($query) => $query->whereHas('lastAddress', fn ($q) => $q->where('approve', 1)))
                    ->exists();
                if (!$addressApprove) {
                    $error = new \Illuminate\Support\MessageBag([
                        'message' => 'Введите и подтвердите адрес доставки',
                    ]);
                    if (request()->ajax() && !request()->pjax()) {
                        return response()->json(['errors' => [
                            'address' => $error->first(),
                        ]], 422);
                    }

                    return redirect()->back()->with(['error' => $error])->withInput();
                }
            }
            if (request()->integer('payment_id') === Installment::PAYMENT_METHOD_ID && !$form->isCreating()) {
                foreach ($orderItems as $orderItem) {
                    if (empty($orderItem['installment_contract_number']) && $orderItem['installment_num_payments']) {
                        $this->emptyContractNumberError($orderItem['product_id']);
                    }
                }
            }
        });
        $form->saving(function (Form $form) {
            CurrencyFacade::setCurrentCurrency($form->input('currency'), false);
            foreach ($form->itemsExtended ?? [] as $key => $item) {
                if (str_starts_with($key, 'new')) {
                    $product = Product::query()->findOrFail($item['product_id']);
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
            $this->updateInventory($form);

            if ($form->isCreating()) {
                event(new OrderCreated($form->model(), null, false));
                event(new OfflinePurchase($form->model()));
            }
            if ((int)$form->input('payment_id') === Installment::PAYMENT_METHOD_ID) {
                $this->saveInstallments($form);
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
            $contractNumber = $form->input("itemsExtended.{$itemExtended->id}.installment_contract_number");
            $monthlyFee = (float)$form->input("itemsExtended.{$itemExtended->id}.installment_monthly_fee");
            $numPayments = (int)$form->input("itemsExtended.{$itemExtended->id}.installment_num_payments");
            $sendNotifications = $form->input("itemsExtended.{$itemExtended->id}.installment_send_notifications") === 'on';
            /** @var Installment $installment */
            $installment = $itemExtended->installment()->firstOrNew();
            if (!$numPayments) {
                if ($installment->exists) {
                    $installment->delete();
                }

                continue;
            }
            $installment->contract_number = $contractNumber;
            $installment->monthly_fee = $monthlyFee;
            $installment->num_payments = $numPayments;
            $installment->send_notifications = $sendNotifications;
            $installment->contract_date = $form->input('installment_contract_date');
            $installment->save();
        }
    }

    private function adminCommentsGrid($orderId)
    {
        $grid = new Grid(new OrderAdminComment());
        $grid->model()->where('order_id', $orderId)->orderBy('id', 'desc');
        $grid->resource('/' . config('admin.route.prefix') . '/order-comments');

        $grid->column('created_at', 'Дата/время создания')->display(fn ($date) => ($date ? date('d.m.Y H:i:s', strtotime($date)) : null))->width(100);
        $grid->column('comment', 'Комментарий')->editable();

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
        });
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid->render();
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
     * Render order history table
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
                <i class="fa fa-archive"></i>&nbsp;&nbsp;' . (new ProcessOrder())->name . '</a></div>');
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
     * Update inventory based on the changes in the provided form.
     */
    private function updateInventory(Form $form): void
    {
        if (empty($form->itemsExtended)) {
            return;
        }
        $inventoryService = app(OrderItemInventoryService::class);
        $prevItemsState = $form->model()->itemsExtended->keyBy('id');
        $currentItemsState = $form->model()->itemsExtended()->get()->keyBy('id');

        foreach ($form->itemsExtended as $item) {
            if ($item[Form::REMOVE_FLAG_NAME]) {
                unset($prevItemsState[$item['id']]);

                continue;
            }
            $prevStockId = $item['id'] ? $prevItemsState[$item['id']]->inventoryNotification?->stock_id : 0;
            $currentStockId = intval($item['stock_id'] ?? 0);
            if (empty($item['id'])) {
                $newOrderItem = $currentItemsState
                    ->where('product_id', $item['product_id'])
                    ->where('size_id', $item['size_id'])
                    ->first();
                $inventoryService->deductSizeFromInventory($newOrderItem, $currentStockId);
            } elseif ($currentStockId !== $prevStockId && !empty($currentStockId)) {
                $prevItemsState[$item['id']]->inventoryNotification?->delete();
                $inventoryService->deductSizeFromInventory($currentItemsState[$item['id']], $currentStockId);
            }
        }
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
    document.querySelectorAll('select.product_id').forEach(function(selectElement) {
        const hiddenProductField = document.createElement('input');
        hiddenProductField.type = 'hidden';
        hiddenProductField.name = selectElement.getAttribute('name');
        hiddenProductField.value = selectElement.value;
        selectElement.parentNode.insertBefore(hiddenProductField, selectElement.nextSibling);
        selectElement.removeAttribute('name');
        selectElement.disabled = true;
    });
    $('select.stock_id.disabled').attr('disabled', true);
    $('select.size_id.disabled').attr('disabled', true);
    $('select.item_status_key.disabled').attr('disabled', true);

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
        $.get('/api/admin/product/data', payload, function (response) {
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

    // size changing
    $(document).on('change', '.itemsExtended.size_id', function () {
        const itemBlock = $(this).parents('.has-many-itemsExtended-form');
        const stocksSelectElement = itemBlock.find('select.stock_id');
        const payload = {
            productId: itemBlock.find('.product_id').val(),
            sizeId: $(this).val(),
            orderItemId: itemBlock.find('.order-item-id').val()
        };
        $.get('/api/admin/stocks', payload, function (stocks) {
            stocksSelectElement.find('option').remove();
            $(stocksSelectElement).select2({
                placeholder: 'Выбрать',
                allowClear: true,
                data: stocks
            });
            if (stocksSelectElement.data('value')) {
                $(stocksSelectElement).val(stocksSelectElement.data('value'));
            }
            $(stocksSelectElement).trigger('change');
        });
    });

    $(document).on('change', '.payment_id', function () {
        $('.installment-field').parents('.form-group').removeClass('hide');
        if ($(this).val() != $installmentPaymentId) {
            $('.installment-field').parents('.form-group').addClass('hide');
        }
    });

    function onReady() {
        // prepare sizes
        $('.itemsExtended.product_id').each(function (index, element) {
            $(element).change();
        });
        // prepare installment fields
        $('.payment_id').change();
    }
    if (document.readyState !== "loading") {
        onReady();
    } else {
        document.addEventListener("DOMContentLoaded", onReady);
    }
});
JS;
    }

    public function changeUserByPhone(ChangeUserByPhoneRequest $request)
    {
        $user = User::query()->where('phone', $request->input('phone'))->first();
        Order::query()->where('id', $request->input('orderId'))->update(['user_id' => $user->id]);

        return $user;
    }

    public function addUserByPhone(Request $request)
    {
        $user = User::query()->where('phone', $request->input('userCreatePhone'))->first();
        if (!$user) {
            $user = User::query()->create([
                'phone' => $request->input('userCreatePhone'),
                'last_name' => $request->input('userCreateLastName'),
                'first_name' => $request->input('userCreateFirstName'),
                'patronymic_name' => $request->input('userCreatePatronymicName'),
            ]);
        }
        Order::query()->where('id', $request->input('orderId'))->update(['user_id' => $user->id]);

        return $user;
    }

    public function updateUserAddress(UserAddressRequest $request)
    {
        $user = User::query()->where('id', $request->input('userId'))->with('lastAddress')->first();
        $order = Order::query()->where('id', $request->input('orderId'))->first();
        if ($user) {
            if ($user->lastAddress) {
                $user->lastAddress->update($request->validated());
            } else {
                $user->addresses()->create($request->validated());
            }
        }
        if ($order) {
            $order->update([
                'country_id' => $user->lastAddress->country_id,
                'city' => $user->lastAddress->city,
                'user_addr' => $user->lastAddress->getAddressRow(),
            ]);
        }

        return $user;
    }

    /**
     * Adds an order comment.
     *
     * @param  Request  $request  The request object.
     * @return OrderAdminComment|null The created order comment, or null if the order ID or comment is missing.
     */
    public function addOrderComment(Request $request): ?OrderAdminComment
    {
        $orderId = $request->input('orderId');
        $comment = $request->input('comment');

        return ($orderId && $comment) ? OrderAdminComment::query()->create([
            'comment' => $comment,
            'order_id' => $orderId,
        ]) : null;
    }

    /**
     * Redirect with an error message when there are no items added to the order.
     */
    protected function emptyItemsError(): RedirectResponse
    {
        $error = new MessageBag([
            'title' => 'Не добавлены товары к заказу!',
            'message' => 'Добавьте товар.',
        ]);

        return back()->with(compact('error'))->withInput();
    }

    /**
     * Throw an error for an empty installment contract number.
     */
    protected function emptyContractNumberError(int|string $productId): never
    {
        $error = new MessageBag([
            'title' => 'Номер договора рассрочки не может быть пустым!',
            'message' => "Заполните номер договора рассрочки для товара {$productId}.",
        ]);

        abort(back()->with(compact('error'))->withInput());
    }
}
