<?php

namespace App\Services\Departures;

use App\Models\Orders\Order;
use App\Services\Departures\BelpostLabelService;

class LabelService
{
    /**
     * Create label
     *
     * @return string
     */
    public function createLabel(int $orderId): string
    {
        $order = Order::where('id', $orderId)->with(['user.passport', 'itemsExtended'])->first();
        $labelService = new BelpostLabelService;
        return $labelService->createLabel($order);
    }
}
