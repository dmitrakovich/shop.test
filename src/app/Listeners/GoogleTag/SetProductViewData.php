<?php

namespace App\Listeners\GoogleTag;

use App\Events\Analytics\ProductView;
use Illuminate\Support\Facades\View;

class SetProductViewData extends AbstractGoogleTagListener
{
    /**
     * Handle the event.
     */
    public function handle(ProductView $event): void
    {
        if (!$event->quick) {
            $this->gtmService->setViewForProduct($event);
        }

        View::share('dataLayer', $this->gtmService->prepareProduct($event->product));
    }
}
