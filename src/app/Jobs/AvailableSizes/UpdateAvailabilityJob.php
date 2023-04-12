<?php

namespace App\Jobs\AvailableSizes;

use App\Models\AvailableSizes;
use App\Models\Product;
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

        // [$attached, $detached] = $this->updateSizes();
        // $this->log("Удалено $detached размеров");
        // $this->log("Добавлено $attached размеров");

        $count = $this->updatePrices();
        $this->log("Обновлены цены для $count товаров");

        $count = $this->restoreProducts();
        $this->log("Опубликовано $count товаров");
    }

    /**
     * Deletes all products that do not have related records in the available_sizes table.
     *
     * @return int The number of deleted products.
     */
    protected function deleteUnavailableProducts(): int
    {
        return DB::table('products')
            ->leftJoin('available_sizes', 'products.id', '=', 'available_sizes.product_id')
            ->whereNull('deleted_at')
            ->whereNull('available_sizes.product_id')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Updates buy and sell prices in the products table based on prices in the available_sizes.
     *
     * @return int The number of updated products.
     */
    protected function updatePrices(): int
    {
       return DB::table('products')
            ->join('available_sizes', 'products.id', '=', 'available_sizes.product_id')
            ->update([
                'products.buy_price' => DB::raw('available_sizes.buy_price'),
                'products.price' => DB::raw('available_sizes.sell_price')
            ]);
    }

    /**
     * Restores all soft deleted products that have related records in the available_sizes table.
     *
     * @return int The number of restored products.
     */
    protected function restoreProducts(): int
    {
        return Product::onlyTrashed()->whereHas('availableSizes')->restore();
    }


    protected function updateSizes(): array
    {
        // сверить размеры
        // восстановить те что вернулись в наличие

        $sizes = [1,4,5,6,7,8];

        $products = Product::withTrashed()->with('availableSizes')->has('availableSizes')->limit(20)->get();
        /** @var Product $product */
        foreach ($products as $product) {
            $product->sizes()->sync($sizes);
        }
        // dump($products);

        return [1, 6];
    }
}
