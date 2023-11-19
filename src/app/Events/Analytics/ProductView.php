<?php

namespace App\Events\Analytics;

use App\Models\Product;
use FacebookAds\Object\ServerSide\ActionSource;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductView extends AbstractAnalyticEvent
{
    use Dispatchable, SerializesModels;

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
