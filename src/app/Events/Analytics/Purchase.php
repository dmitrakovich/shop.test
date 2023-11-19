<?php

namespace App\Events\Analytics;

use App\Models\Orders\Order;
use FacebookAds\Object\ServerSide\ActionSource;

class Purchase extends AbstractAnalyticEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Order $order,
        public string $actionSource = ActionSource::WEBSITE
    ) {
        $this->setAnalyticData();
    }
}
