<?php

namespace App\Jobs\AvailableSizes;

use App\Enums\Product\ProductLabel;
use App\Events\Products\ProductUpdated;
use App\Models\AvailableSizes;
use App\Models\Product;
use App\Models\Size;
use App\Services\LogService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdateAvailabilityJob extends AbstractAvailableSizesJob
{
    /**
     * Max items for one sql query for add/delete product sizes
     */
    const SIZE_QUERY_CHUNK = 200;

    /**
     * Update data for log
     */
    protected array $logData = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private LogService $logService) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        UpdateAvailableSizesTableJob::dispatchSync();

        $this->updateProductsOneCIdFromAvailableSizes();

        $count = $this->deleteUnavailableProducts();
        $this->log("Снято с публикации $count товаров");

        [$attached, $detached] = $this->updateSizes();
        $this->log("Удалено $detached размеров");
        $this->log("Добавлено $attached размеров");

        // $count = $this->updatePrices();
        // $this->log("Обновлены цены для $count товаров");

        $count = $this->restoreProducts();
        $this->log("Опубликовано $count товаров");

        $this->writeLog();
        $this->log('Обновление успешно завершено!');
    }

    /**
     * Update one_c_id in products table from available_sizes table.
     */
    public function updateProductsOneCIdFromAvailableSizes(): void
    {
        $timestamp = now()->startOfMinute();

        DB::update(<<<'SQL'
            UPDATE products p
            SET p.one_c_id = (
                SELECT asz.one_c_product_id
                FROM available_sizes asz
                WHERE asz.product_id = p.id
                LIMIT 1
            ), p.updated_at = ?
            WHERE p.one_c_id IS NULL AND p.deleted_at IS NULL;
        SQL, [$timestamp]);

        Product::query()
            ->with(['category', 'productFromOneC', 'media', 'countryOfOrigin', 'manufacturer'])
            ->where('updated_at', $timestamp)
            ->whereNotNull('one_c_id')
            ->each(fn (Product $product) => event(new ProductUpdated($product)));
    }

    /**
     * Deletes all products that do not have related records in the available_sizes table.
     *
     * @return int The number of deleted products.
     */
    protected function deleteUnavailableProducts(): int
    {
        $query = DB::table('products')
            ->leftJoin('available_sizes', 'products.id', '=', 'available_sizes.product_id')
            ->whereNull('products.deleted_at')
            ->where(fn (Builder $query) => $this->filterExcludedLabels($query))
            ->whereNull('available_sizes.product_id');

        $this->logData['deleteProducts'] = $query->pluck('products.id')->toArray();

        return $query->update(['deleted_at' => now(), 'updated_at' => now()]);
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
                'products.price' => DB::raw('available_sizes.sell_price'),
            ]);
    }

    /**
     * Restores all soft deleted products that have related records in the available_sizes table.
     *
     * @return int The number of restored products.
     */
    protected function restoreProducts(): int
    {
        $query = Product::onlyTrashed()
            ->whereHas('availableSizes')
            ->where(fn (Builder $query) => $this->filterExcludedLabels($query));

        $this->logData['restoreProducts'] = $query->pluck('id')->toArray();

        return $query->restore();
    }

    protected function updateSizes(): array
    {
        $existingSizes = [];
        DB::table('product_attributes')
            ->join('products', 'products.id', '=', 'product_attributes.product_id')
            ->where('attribute_type', Size::class)
            ->where(fn (Builder $query) => $this->filterExcludedLabels($query))
            ->get(['product_id', 'attribute_id'])
            ->each(function (\stdClass $attribute) use (&$existingSizes) {
                $existingSizes[$attribute->product_id][] = $attribute->attribute_id;
            });

        $availableSizes = DB::table('available_sizes')
            ->join('products', 'products.id', '=', 'available_sizes.product_id')
            ->whereNotNull('product_id')
            ->where(fn (Builder $query) => $this->filterExcludedLabels($query))
            ->selectRaw('product_id, ' . implode(', ', AvailableSizes::getSumWrappedSizeFields()))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id')
            ->map(function (\stdClass $productSizes) {
                unset($productSizes->product_id);
                $filteredSizes = array_filter((array)$productSizes, fn ($size) => (int)$size > 0);

                return array_map(
                    fn ($sizeField) => AvailableSizes::convertFieldToSizeId($sizeField),
                    array_keys($filteredSizes)
                );
            })
            ->toArray();

        $detached = $this->arrayDiffAssocRecursive($existingSizes, $availableSizes);
        $attached = $this->arrayDiffAssocRecursive($availableSizes, $existingSizes);

        $this->logData['addSizes'] = $attached;
        $this->logData['deleteSizes'] = $detached;

        foreach (array_chunk($detached, self::SIZE_QUERY_CHUNK, true) as $detachChunk) {
            $detachQuery = DB::table('product_attributes');
            foreach ($detachChunk as $productId => $sizeIds) {
                foreach ($sizeIds as $sizeId) {
                    $detachQuery->orWhere(function (Builder $query) use ($productId, $sizeId) {
                        $query->where('product_id', $productId)
                            ->where('attribute_type', Size::class)
                            ->where('attribute_id', $sizeId);
                    });
                }
            }
            $detachQuery->delete();
        }

        foreach (array_chunk($attached, self::SIZE_QUERY_CHUNK * 10, true) as $attachChunk) {
            $attachData = [];
            foreach ($attachChunk as $productId => $sizeIds) {
                foreach ($sizeIds as $sizeId) {
                    $attachData[] = [
                        'product_id' => $productId,
                        'attribute_type' => Size::class,
                        'attribute_id' => $sizeId,
                    ];
                }
            }
            DB::table('product_attributes')->insert($attachData);
        }

        return [
            array_sum(array_map('count', $attached)),
            array_sum(array_map('count', $detached)),
        ];
    }

    /**
     * Recursively finds differences between two arrays by keys and values.
     */
    private function arrayDiffAssocRecursive(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (!isset($array2[$key])) {
                $difference[$key] = $value;
            } elseif ($array2[$key] !== $value) {
                $difference[$key] = array_diff($value, $array2[$key]);
            }
        }

        return array_filter($difference);
    }

    /**
     * Write log data to DB
     */
    protected function writeLog(): void
    {
        $this->logService->logAvailabilityUpdate(
            $this->logData['restoreProducts'],
            $this->logData['deleteProducts'],
            $this->logData['addSizes'],
            $this->logData['deleteSizes'],
        );
    }

    private function filterExcludedLabels(Builder $query): Builder
    {
        return $query->whereNotIn('products.label_id', ProductLabel::getNotUpdateLabels())
            ->orWhereNull('products.label_id');
    }
}
