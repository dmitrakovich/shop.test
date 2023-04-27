<?php

namespace App\Services\Departures;

use App\Models\Orders\Order;

class LabelService
{
    /**
     * Create label
     */
    public function createLabel(int $orderId): string
    {
        $order = Order::where('id', $orderId)->with(['user.passport', 'itemsExtended'])->first();
        $labelService = new BelpostLabelService;

        return $labelService->createLabel($order);
    }
}
