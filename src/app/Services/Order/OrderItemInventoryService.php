<?php

namespace App\Services\Order;

use App\Models\Bots\Telegram\TelegraphChat;
use App\Models\Orders\OrderItem;
use App\Notifications\OrderItemInventoryNotification;

class OrderItemInventoryService
{
    public function handleChangeItemStatus(OrderItem $orderItem): void
    {
        if ($this->shouldSendNotification($orderItem)) {
            // todo: send notification
            // dd($orderItem);

            //todo: get chat by stock

            /** @var TelegraphChat */
            $chat = TelegraphChat::find(4);


            $chat->notify(new OrderItemInventoryNotification($orderItem));
        }
    }

    protected function shouldSendNotification(OrderItem $orderItem): bool
    {
        return true;

        //todo: check log model

        return in_array($orderItem->status_key, ['new', 'canceled', 'confirmed', 'complete', 'installment']);
    }
}
