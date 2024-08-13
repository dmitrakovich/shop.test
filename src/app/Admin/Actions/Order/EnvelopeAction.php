<?php

namespace App\Admin\Actions\Order;

use App\Models\Orders\Order;
use App\Services\Order\EnvelopeService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class EnvelopeAction extends Action
{
    public $name = 'Конверт';

    protected $selector = '.js-envelopeAction';

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
        $envelopeService = new EnvelopeService();
        $order = Order::where('id', $request->orderId)->with([
            'user' => fn ($query) => $query->with('lastAddress'),
        ])->first();
        $file = $envelopeService->createEnvelope($order);

        return $this->response()->success('Конверт успешно создан')->download($file);
    }

    /**
     * Html installment form
     */
    public function html(): string
    {
        return <<<HTML
        <div class="btn-group pull-right" style="margin-right: 5px">
            <a target="_blank" class="js-envelopeAction btn btn-sm btn-default" data-order-id="$this->orderId">
                $this->name
            </a>
        </div>
        HTML;
    }
}
