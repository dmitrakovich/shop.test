<?php

namespace App\Listeners\GoogleTag;

use App\Events\Analytics\Purchase;

class SetPurchaseData extends AbstractGoogleTagListener
{
    /**
     * Handle the event.
     */
    public function handle(Purchase $event): void
    {
        $this->gtmService->setViewForOrder();
        $this->gtmService->setPurchaseEvent($event);
    }
}
