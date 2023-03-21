<?php

namespace App\Services;

use App\Enums\StockTypeEnum;
use App\Models\City;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;

class StockService
{
    /**
     * Shop collection
     */
    public function getShops(): Collection
    {
        return Stock::where('type', StockTypeEnum::SHOP)->get();
    }

    /**
     * Get cities where there are shops
     */
    public function getCitiesWithShops(): Collection
    {
        return City::whereHas('stocks', fn ($query) => $query->where('type', StockTypeEnum::SHOP))
            ->with(['stocks' => fn ($query) => $query->where('type', StockTypeEnum::SHOP)])
            ->get();
    }
}
