<?php

namespace App\Listeners\GoogleTag;

use App\Services\GoogleTagManagerService;

abstract class AbstractGoogleTagListener
{
    /**
     * Create the event listener.
     */
    public function __construct(protected GoogleTagManagerService $gtmService) {}
}
