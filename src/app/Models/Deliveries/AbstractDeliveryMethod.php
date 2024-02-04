<?php

namespace Deliveries;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class AbstractDeliveryMethod
{
    /**
     * DeliveryMethod id
     */
    public const ID = 0;

    public function __construct(
        private readonly Model $model
    ) {
    }

    public function getAdditionalInfo(): View|string|null
    {
        return null;
    }
}
