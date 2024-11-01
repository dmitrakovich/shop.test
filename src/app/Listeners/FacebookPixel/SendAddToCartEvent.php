<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\AddToCart;
use FacebookAds\Object\ServerSide\CustomData;

class SendAddToCartEvent extends AbstractFacebookPixelListener
{
    /**
     * Handle the event.
     */
    public function handle(AddToCart $addToCartEvent): void
    {
        $product = $addToCartEvent->product;
        $customData = (new CustomData())
            ->setValue($product->getPrice('USD'))
            ->setCurrency('USD')
            ->setContentIds([$product->id])
            ->setContentType('product');

        $event = $this->generateEvent($addToCartEvent)
            ->setEventName('AddToCart')
            ->setCustomData($customData);

        $this->sendEvent($event);
    }
}
