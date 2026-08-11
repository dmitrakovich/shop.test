<?php

namespace App\Repositories;

use App\Enums\StockType;
use App\Models\City;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class StockRepository
{
    public function __construct(private readonly Stock $model) {}

    /**
     * @return Collection<int, Stock>
     */
    public function getShops(): Collection
    {
        return $this->model->newQuery()
            ->active()
            ->where('type', StockType::SHOP)
            ->get();
    }

    /**
     * @return Collection<int, City>
     */
    public function getCitiesWithShops(): Collection
    {
        return City::query()
            ->whereHas(
                'stocks',
                fn (Builder $query) => $query->where('is_active', true)->where('type', StockType::SHOP)
            )
            ->with([
                'stocks' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('type', StockType::SHOP),
            ])
            ->get();
    }

    /**
     * Active shop addresses for selects / PVZ, optionally keeping the current value.
     *
     * @return SupportCollection<int|string, string|null>
     */
    public function shopAddressOptions(?int $includeStockId = null): SupportCollection
    {
        return $this->model->newQuery()
            ->where('type', StockType::SHOP)
            ->where(fn (Builder $query) => $query
                ->where('is_active', true)
                ->when($includeStockId, fn (Builder $query) => $query->orWhere('id', $includeStockId)))
            ->pluck('address', 'id');
    }

    /**
     * @return SupportCollection<int|string, string>
     */
    public function availabilityCheckInternalNames(): SupportCollection
    {
        return $this->model->newQuery()
            ->active()
            ->where('check_availability', true)
            ->pluck('internal_name', 'id');
    }

    /**
     * Map of 1C stock id => local stock id for availability sync.
     *
     * @return array<int, int>
     */
    public function oneCIdMapForAvailabilitySync(): array
    {
        return $this->model->newQuery()
            ->active()
            ->where('check_availability', true)
            ->whereNotNull('one_c_id')
            ->pluck('id', 'one_c_id')
            ->all();
    }
}
