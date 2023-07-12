<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Services\Order\InstallmentOrderService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class InstallmentForm extends Action
{
    public $name = 'Создать бланк рассрочки';

    protected $selector = '.js-installmentForm';

    protected ?int $orderId = null;

    public function __construct(int $orderId = null)
    {
        parent::__construct();
        $this->orderId = $orderId;
    }

    /**
     * Action hadle
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $order = Order::where('id', $request->orderId)->with(['user.passport'])->first();
        if (!isset($order->user)) {
            throw new \Exception('Привяжите клиента к заказу');
        }
        if (!isset($order->user->passport)) {
            throw new \Exception('Заполните паспортные данные клиента');
        }
        $installmentService = new InstallmentOrderService;
        $file = $installmentService->createInstallmentForm($request->orderId);

        return $this->response()->success('Бланк рассрочки успешно создан')->download($file);
    }

    /**
     * Html installment form
     */
    public function html(): string
    {
        return <<<HTML
        <div class="btn-group pull-right" style="margin-right: 5px">
            <a target="_blank" class="js-installmentForm btn btn-sm btn-default" data-order-id="$this->orderId">
                $this->name
            </a>
        </div>
        HTML;
    }
}
