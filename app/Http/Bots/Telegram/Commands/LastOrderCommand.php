<?php

namespace App\Http\Bots\Telegram\Commands;

use App\Models\Order;
use Telegram\Bot\Commands\Command;

/**
 * Class LastOrderCommand.
 */
class LastOrderCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected $name = 'lastOrder';

    /**
     * @var string Command Description
     */
    protected $description = 'Время и номер последнего заказа на сайте';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $order = Order::latest()->first(['id', 'user_name', 'created_at']);
        $text = "заказ №$order->id, на имя $order->user_name, создан в $order->created_at";

        $this->replyWithMessage(compact('text'));
    }
}
