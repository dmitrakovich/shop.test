<?php

namespace App\Listeners\OneC;

use App\Events\Products\ProductCreated;
use App\Events\Products\ProductUpdated;
use Illuminate\Support\Facades\App;

class UpdateProduct
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCreated|ProductUpdated $event): void
    {
        if (!App::isProduction()) {
            return;
        }
        $product = $event->product;
        $productFromOneC = $product->productFromOneC;
        if (!$productFromOneC) {
            return;
        }

        $productFromOneC->update(array_filter([
            'SP6111' => url($product->getUrl()),
            'SP6116' => $product->getFirstMediaUrl(conversionName: 'catalog'),
            'SP6122' => $product->countryOfOrigin?->name,
            'SP6123' => $product->manufacturer?->name,
            'SP6124' => $product->category->name,
            'SP6125' => $product->collection->name,
            'SP6142' => $product->id,
            'SP6155' => $product->price,
            'SP6156' => $product->getFixedOldPrice(),
            'SP6157' => $product->getDiscountPercentage(),
        ], fn ($value) => !is_null($value)));
    }
}
