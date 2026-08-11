<?php

namespace Deliveries;

use App\Repositories\StockRepository;
use Illuminate\Contracts\View\View;

class ShopPvz extends AbstractDeliveryMethod
{
    /**
     * DeliveryMethod id
     */
    public const ID = 6;

    public function getAdditionalInfo(): View|string|null
    {
        $shops = app(StockRepository::class)->shopAddressOptions();

        return view('shop.deliveries.additional-info.shop-pvz', compact('shops'));
    }
}
