<?php

namespace App\Listeners\Promo;

use App\Events\User\UserLogin;
use App\Services\SaleService;

class ApplyPendingPromocode
{
    /**
     * Create the event listener.
     */
    public function __construct(private readonly SaleService $saleService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserLogin $event): void
    {
        $this->saleService->applyPendingPromocode($event->user);
    }
}
