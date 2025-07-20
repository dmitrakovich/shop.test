<?php

namespace App\Admin\Actions\Order;

use App\Enums\User\BanReason;
use App\Models\Orders\Order;
use Encore\Admin\Actions\Response;
use Encore\Admin\Actions\RowAction;

class BanDeviceAction extends RowAction
{
    public $name = 'Забанить';

    /**
     * @return Response
     */
    public function handle(Order $order)
    {
        $order->device->ban(BanReason::BY_ADMIN);

        return $this->response()->success('Устройство, с которого был сделан заказ, успешно заблокировано');
    }
}
