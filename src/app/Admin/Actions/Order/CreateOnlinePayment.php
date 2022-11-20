<?php

namespace App\Admin\Actions\Order;

use App\Enums\Payment\OnlinePaymentMethodEnum;
use App\Services\Payment\PaymentService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class CreateOnlinePayment extends Action
{
    protected $selector = '.report-posts';

    public $name = 'Создать платеж';

    private $orderId;

    public function __construct(?int $orderId = null)
    {
        parent::__construct();
        if ($orderId) {
            $this->orderId = $orderId;
        }
    }

    public function handle(
        Request $request
    ) {
        $paymentService = new PaymentService;
        $data = $request->all();
        $paymentService->createOnlinePayment($data);

        return $this->response()->success('Счет на оплату создан!')->refresh();
    }

    public function form()
    {
        $this->text('order_id', 'Номер заказа')->default($this->orderId ?? null)->readonly();
        $this->select('method_enum_id', 'Способ оплаты')->options(OnlinePaymentMethodEnum::list())->default(OnlinePaymentMethodEnum::ERIP->value);
        $this->text('amount', 'Сумма платежа')->rules('required');
        $this->textarea('comment', 'Комментарий');
        $this->radio('send_sms', 'Отправлять SMS оповещение')->options([1 => 'Да', 0 => 'Нет'])->default(1);
    }

    public function html()
    {
        return "<div class='text-center'><a class='report-posts btn btn-success'>Создать платеж</a></div>";
    }
}
