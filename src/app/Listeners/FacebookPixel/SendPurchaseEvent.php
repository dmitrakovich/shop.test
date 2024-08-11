<?php

namespace App\Listeners\FacebookPixel;

use App\Events\Analytics\OfflinePurchase;
use App\Events\Analytics\Purchase;
use App\Facades\Currency;
use FacebookAds\Object\ServerSide\CustomData;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseEvent extends AbstractFacebookPixelListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(Purchase $purchaseEvent): void
    {
        $events = [];
        $order = $purchaseEvent->order;
        $customData = (new CustomData)
            ->setValue(Currency::convert($order->getTotalPrice(), 'USD'))
            ->setCurrency('USD')
            ->setContentIds($order->items->pluck('product_id')->toArray())
            ->setContentType('product');

        if ($order->isOneClick() && !$purchaseEvent instanceof OfflinePurchase) {
            $events[] = $this->generateEvent($purchaseEvent)
                ->setEventName('AddToCart')
                ->setCustomData($customData);
        }

        $events[] = $this->generateEvent($purchaseEvent)
            ->setEventId($order->id)
            ->setEventName('Purchase')
            ->setCustomData($customData);

        $this->sendEvents($events);
    }
}
