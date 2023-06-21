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
     * Write message in logs
     */
    protected function log(string $message, string $level = 'info'): void
    {
        //
    }
}
