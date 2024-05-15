<?php

namespace App\Listeners\Promo;

use App\Services\SaleService;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
    public function handle(Login $event): void
    {
        $this->saleService->applyPendingPromocode($event->user);
    }
}
