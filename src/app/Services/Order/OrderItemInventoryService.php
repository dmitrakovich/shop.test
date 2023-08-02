<?php

namespace App\Services\Order;

use App\Models\Orders\OrderItem;
use App\Notifications\OrderItemInventoryNotification;

/**
 * Class OrderItemInventoryService
 * @package App\Services\Order
 */
class OrderItemInventoryService
{
    /**
     * Possible statuses for which notifications are sent.
     */
    const STATUSES_FOR_NOTIFICATIONS = [
        'new',
        'canceled',
        'confirmed',
        'complete',
        'installment',
        'return',
        'return_fitting',
    ];

    /**
     * Handle the change of status for an order item and send notification if required.
     */
    public function handleChangeItemStatus(OrderItem $orderItem): void
    {
        if ($this->shouldSendNotification($orderItem)) {
            $chat = $orderItem->invertoryNotification->stock->chat;
            $chat->notify(new OrderItemInventoryNotification($orderItem));
        }
    }

    /**
     * Check if a notification should be sent for the given order item and status.
     */
    protected function shouldSendNotification(OrderItem $orderItem): bool
    {
        if (empty($orderItem->invertoryNotification)) {
            return false;
        }

        $status = $orderItem->status_key;
        $notification = $orderItem->invertoryNotification;
        $dateField = $notification::getDateFieldByStatus($status);

        return in_array($status, self::STATUSES_FOR_NOTIFICATIONS) && is_null($notification->{$dateField});
    }
}
