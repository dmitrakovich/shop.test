<?php

namespace App\Services\Analytics;

use App\Enums\Order\OrderItemStatus;
use App\Enums\Order\OrderStatus;

final class AnalyticStatusGroups
{
    /**
     * @var array<string, list<OrderStatus>>
     */
    private const array ORDER_STATUSES = [
        'accepted' => [OrderStatus::NEW],
        'in_progress' => [
            OrderStatus::IN_WORK,
            OrderStatus::WAIT_PAYMENT,
            OrderStatus::PAID,
            OrderStatus::ASSEMBLED,
            OrderStatus::PACKAGING,
            OrderStatus::READY,
            OrderStatus::SENT,
            OrderStatus::FITTING,
            OrderStatus::CONFIRMED,
        ],
        'purchased' => [
            OrderStatus::COMPLETED,
            OrderStatus::INSTALLMENT,
            OrderStatus::PARTIAL_COMPLETED,
        ],
        'canceled' => [OrderStatus::CANCELED],
        'returned' => [OrderStatus::RETURN, OrderStatus::RETURN_FITTING],
        'lost' => [
            OrderStatus::CANCELED,
            OrderStatus::RETURN,
            OrderStatus::RETURN_FITTING,
        ],
    ];

    /**
     * @var array<string, list<OrderItemStatus>>
     */
    private const array ITEM_STATUSES = [
        'accepted' => [OrderItemStatus::NEW],
        'in_progress' => [
            OrderItemStatus::PACKAGING,
            OrderItemStatus::SENT,
            OrderItemStatus::FITTING,
            OrderItemStatus::CONFIRMED,
        ],
        'purchased' => [OrderItemStatus::COMPLETED, OrderItemStatus::INSTALLMENT],
        'canceled' => [OrderItemStatus::CANCELED],
        'returned' => [OrderItemStatus::RETURN, OrderItemStatus::RETURN_FITTING],
        'lost' => [
            OrderItemStatus::CANCELED,
            OrderItemStatus::RETURN,
            OrderItemStatus::RETURN_FITTING,
        ],
    ];

    public static function orderStatusIds(string $bucket): string
    {
        return implode(',', array_map(
            static fn (OrderStatus $status): int => $status->value,
            self::ORDER_STATUSES[$bucket],
        ));
    }

    public static function itemStatusIds(string $bucket): string
    {
        return implode(',', array_map(
            static fn (OrderItemStatus $status): int => $status->value,
            self::ITEM_STATUSES[$bucket],
        ));
    }
}
