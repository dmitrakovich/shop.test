<?php

namespace App\Jobs\AvailableSizes;

use App\Models\Stock;

class UpdateAvailableSizesFullTableJob extends UpdateAvailableSizesTableJob
{
    /**
     * Table for insert data
     */
    protected string $availableSizesTable = 'available_sizes_full';

    /**
     * Set current stocks in pairs: one_c_id => stock_id
     */
    protected function setCurrentStockIds(): void
    {
        $this->stockIds = Stock::query()
            ->whereNotNull('one_c_id')
            ->pluck('id', 'one_c_id')
            ->toArray();
    }

    /**
     * Update available sizes of products based on orders.
     */
    protected function updateAvailableSizesFromOrders(array &$availableSizes): int
    {
        return 0;
    }

    /**
     * Update available sizes of products based on defective products.
     */
    protected function updateAvailableSizesByDefectiveProducts(array &$availableSizes): int
    {
        return 0;
    }

    /**
     * The full table sync runs silently and does not publish catalog availability metrics.
     */
    protected function recordsCatalogMetrics(): bool
    {
        return false;
    }

    /**
     * The full table sync runs silently.
     *
     * @param  array<string, scalar|null>  $context
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        //
    }
}
