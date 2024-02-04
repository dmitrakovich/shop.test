<?php

namespace Deliveries;

use App\Enums\StockTypeEnum;
use App\Models\Stock;
use Illuminate\Contracts\View\View;

class ShopPvz extends AbstractDeliveryMethod
{
    /**
     * DeliveryMethod id
     */
    public const ID = 6;

    public function getAdditionalInfo(): View|string|null
    {
        $shops = Stock::query()
            ->where('type', StockTypeEnum::SHOP)
            ->pluck('address', 'id');

        return view('shop.deliveries.additional-info.shop-pvz', compact('shops'));
    }
}
