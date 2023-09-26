<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Services\Order\BuyoutOrderService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class BuyoutFormAction extends Action
{
    public $name = 'Бланк выкупа';

    protected $selector = '.js-buyoutFormAction';

    protected ?int $orderId = null;

    public function __construct(?int $orderId = null)
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
        $buyoutService = new BuyoutOrderService;
        $order = Order::where('id', $request->orderId)->with([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status_key', ['installment', 'packaging', 'pickup', 'sent', 'fitting', 'complete', 'return', 'return_fitting'])
                ->with('installment'),
            'onlinePayments',
            'delivery',
            'user' => fn ($query) => $query->with('lastAddress'),
        ])->first();
        if (!count($order->items)) {
            throw new \Exception('В заказе нет товаров со статусом Забран');
        }
        $file = $buyoutService->createBuyoutForm($order);

        return $this->response()->success('Бланк выкупа успешно создан')->download($file);
    }

    /**
     * Html installment form
     */
    public function html(): string
    {
        return <<<HTML
        <div class="btn-group pull-right" style="margin-right: 5px">
            <a target="_blank" class="js-buyoutFormAction btn btn-sm btn-default" data-order-id="$this->orderId">
                $this->name
            </a>
        </div>
        HTML;
    }
}
