<?php

namespace Deliveries;

use Illuminate\Contracts\View\View;

class BelpostCourierFitting extends AbstractDeliveryMethod
{
    /**
     * DeliveryMethod id
     */
    public const ID = 1;

    public function getAdditionalInfo(): View|string|null
    {
        return view('shop.deliveries.additional-info.belpost-courier-fitting');
    }
}
