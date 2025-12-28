<?php

namespace App\Listeners\User;

use App\Enums\CurrencyCode;
use App\Enums\Order\OrderItemStatus;
use App\Events\Order\OrderItemCompleted;
use App\Models\Orders\OrderItem;
use App\Models\User\Group;
use App\Models\User\User;

class RecalculateUserGroup
{
    public function handle(OrderItemCompleted $event): void
    {
        if (!($user = $event->orderItem->order->user) instanceof User) {
            return;
        }

        // todo: хранить все в BYN + сохранять текущий курс
        $orderIds = $user->orders()->where('currency', CurrencyCode::BYN)->pluck('id');

        $purchasesSum = OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->where('status', OrderItemStatus::COMPLETED)
            ->sum('current_price');

        $newGroupId = Group::getGroupIdByPurchaseSum($purchasesSum);
        if ($newGroupId > $user->group_id) {
            $user->update(['group_id' => $newGroupId]);
        }
    }
}
