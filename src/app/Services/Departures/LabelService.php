<?php

namespace App\Services\Departures;

use App\Enums\Order\OrderItemStatus;
use App\Models\Orders\Order;

class LabelService
{
    /**
     * Create label
     */
    public function createLabel(int $orderId): string
    {
        $order = Order::where('id', $orderId)->with([
            'itemsExtended' => fn ($query) => $query
                ->whereIn('status', OrderItemStatus::departureStatuses())
                ->with('installment'),
            'onlinePayments',
            'delivery',
            'user' => fn ($query) => $query->with('lastAddress'),
        ])->first();
        $labelService = new BelpostLabelService();

        return $labelService->createLabel($order);
    }
}
