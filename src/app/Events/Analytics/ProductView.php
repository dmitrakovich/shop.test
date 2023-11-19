<?php

namespace App\Events\Analytics;

use App\Models\Product;
use FacebookAds\Object\ServerSide\ActionSource;

class ProductView extends AbstractAnalyticEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Product $product,
        public bool $quick = false,
        public string $actionSource = ActionSource::WEBSITE
    ) {
        $this->setAnalyticData();
    }
}
