<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\ProductView;
use FacebookAds\Object\ServerSide\CustomData;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProductViewEvent extends AbstractFacebookPixelListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ProductView $productViewEvent): void
    {
        $product = $productViewEvent->product;
        $customData = (new CustomData())
            ->setValue($product->getPrice('USD'))
            ->setCurrency('USD')
            ->setContentIds([$product->id])
            ->setContentType('product');

        $event = $this->generateEvent($productViewEvent)
            ->setEventName('ViewContent')
            ->setCustomData($customData);

        $this->sendEvent($event);
    }
}
