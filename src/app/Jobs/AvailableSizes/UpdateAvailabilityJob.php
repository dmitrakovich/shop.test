<?php

namespace App\Jobs\AvailableSizes;

use App\Models\AvailableSizes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdateAvailabilityJob extends AbstractAvailableSizesJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // UpdateSizesAvailabilitiesTableJob::dispatchSync();

        // $count = AvailableSizes::removeEmptySizes();
        // $this->log("Удалено $count записей с пустыми размерами в таблице наличия");

        $count = $this->deleteUnavailableProducts();
        $this->log("Снято с публикации $count товаров");

        // сверить размеры
        // сверить цены ?
        // восстановить те что вернулись в наличие
    }

    /**
     * Detale products that are not in the list of available sizes
     */
    protected function deleteUnavailableProducts(): int
    {
        return DB::table('products')
            ->leftJoin('available_sizes', 'products.id', '=', 'available_sizes.product_id')
            ->whereNull('deleted_at')
            ->whereNull('available_sizes.product_id')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }
}
