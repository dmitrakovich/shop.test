<?php

namespace App\Jobs\AvailableSizes;

use App\Jobs\Ssh\CreateTunnelJob;
use App\Jobs\Ssh\DestroyTunnelJob;
use App\Models\AvailableSizes;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Config;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateAvailableSizesTableJob extends AbstractAvailableSizesJob
{
    /**
     * Table name in 1C, contains the quantity in stock
     */
    const ONE_C_STOCK_QUANTITY_TABLE = 'SC5925';

    /**
     * Minimum number of records expected from 1C.
     *
     * If the actual number of records retrieved from 1C is less than this value,
     * an exception should be thrown.
     */
    const MIN_EXPECTED_RECORDS = 1000;

    /**
     * Table for insert data
     */
    protected string $availableSizesTable = 'available_sizes';

    /**
     * Current product identificators
     */
    protected array $productIds = [];

    /**
     * Current brand identificators
     */
    protected array $brandIds = [];

    /**
     * Current stock identificators
     */
    protected array $stockIds = [];

    /**
     * Current catagory identificators
     */
    protected array $catagoryIds = [];

    /**
     * List of english symbols for convert wrong sku
     */
    protected array $engSymbols = ['a', 'b', 'c', 'e', 'h', 'k', 'm', 'o', 'p', 't', 'x'];

    /**
     * List of russian symbols for convert wrong sku
     */
    protected array $rusSymbols = ['а', 'в', 'с', 'е', 'н', 'к', 'м', 'о', 'р', 'т', 'х'];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->log('Подготовка к синхронизации');
        $this->setCurrentStockIds();
        $this->setCurrentProductIds();
        $this->setCurrentBrandIds();
        $this->setCurrentCategoryIds();

        $this->log('Получение наличия с 1С');
        $availableSizes = $this->getAvailableSizesFrom1C();

        $count = $this->filterAvailableSizes($availableSizes);
        $this->log("Отфильтровано $count записей с неподходящими категориями или пустыми артикулами");

        $count = $this->updateAvailableSizesFromOrders($availableSizes);
        $this->log("Обновлено $count доступных размеров товаров на основе заказов");

        $count = $this->removeEmptySizes($availableSizes);
        $this->log("Удалено $count записей с пустыми размерами");

        $this->notifyOfflineOrders($availableSizes);

        $this->log('Запись полученных и сопоставленных данных в базу');
        DB::table($this->availableSizesTable)->truncate();
        DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        DB::table($this->availableSizesTable)->insert($availableSizes);
        DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $this->log('Таблица с наличием успешно обновлена');
    }

    /**
     * Retrieve available sizes data from 1C and prepare it for processing.
     *
     * @throws \Exception If there is an error retrieving data from 1C or if the retrieved record count is below the expected minimum.
     */
    protected function getAvailableSizesFrom1C(): array
    {
        CreateTunnelJob::dispatchSync();

        $availableSizes = [];
        DB::connection('sqlsrv')
            ->table(self::ONE_C_STOCK_QUANTITY_TABLE)
            ->select($this->getStockQuantityFields())
            ->orderBy('ROW_ID')
            ->whereIn('SP5900', array_keys($this->stockIds))
            ->each(function (\stdClass $stockUnit) use (&$availableSizes) {
                $availableSizes[] = $this->prepareAvailableSizesData($stockUnit);
            });

        DestroyTunnelJob::dispatchSync();

        if (count($availableSizes) < self::MIN_EXPECTED_RECORDS) {
            throw new \Exception('Error retrieving data from 1C, received ' . count($availableSizes) . ' records');
        }

        return $availableSizes;
    }

    /**
     * List of 1C stock quantity table's fields
     */
    protected function getStockQuantityFields(): array
    {
        return [
            'ROW_ID',
            'SP5898 as one_c_product_id',
            'SP5902 as brand_id',
            'DESCR as category_name',
            'SP5900 as stock_id',
            'SP5896 as sku',
            'SP5904 as buy_price',
            'SP5903 as sell_price',
            'SP5905 as size_none',
            'SP5906 as size_31',
            'SP5907 as size_32',
            'SP5908 as size_33',
            'SP5909 as size_34',
            'SP5910 as size_35',
            'SP5911 as size_36',
            'SP5912 as size_37',
            'SP5913 as size_38',
            'SP5914 as size_39',
            'SP5915 as size_40',
            'SP5916 as size_41',
            'SP5917 as size_42',
            'SP5918 as size_43',
            'SP5919 as size_44',
            'SP5920 as size_45',
            'SP5921 as size_46',
            'SP5922 as size_47',
            'SP5923 as size_48',
        ];
    }

    /**
     * Set current stocks in pairs: one_c_id => stock_id
     */
    protected function setCurrentStockIds(): void
    {
        $this->stockIds = Stock::query()
            ->where('check_availability', true)
            ->whereNotNull('one_c_id')
            ->pluck('id', 'one_c_id')
            ->toArray();
    }

    /**
     * Set current product ids in such structure: array[brandId][preparedSku] => productId
     */
    protected function setCurrentProductIds(): void
    {
        DB::table('products')
            ->select(['id', 'brand_id', 'sku'])
            ->orderBy('id')
            ->each(function ($product) {
                $preparedSku = $this->prepareSkuToCompare($product->sku);
                $this->productIds[$product->brand_id][$preparedSku] = $product->id;
            });
    }

    /**
     * Set current brands in pairs: one_c_id => brand_id
     */
    protected function setCurrentBrandIds(): void
    {
        $this->brandIds = Brand::query()
            ->whereNotNull('one_c_id')
            ->pluck('id', 'one_c_id')
            ->toArray();
    }

    /**
     * Set current categories in pairs: one_c_category_name => category_id
     */
    protected function setCurrentCategoryIds(): void
    {
        $this->catagoryIds = Category::query()
            ->whereNotNull('one_c_name')
            ->pluck('id', 'one_c_name')
            ->toArray();
    }

    /**
     * Remove excess characters & convert to lowercase
     */
    protected function prepareSkuToCompare(string $sku): string
    {
        $remove = [' ', '-', '.', '_', '*'];

        return mb_strtolower(str_replace($remove, '', $sku));
    }

    /**
     * Prepare size availability data for database insertion
     */
    protected function prepareAvailableSizesData(\stdClass $stockUnit): array
    {
        $sku = trim($stockUnit->sku);
        $categoryName = trim($stockUnit->category_name);
        $brandId = $this->getCurrentBrandId((int)$stockUnit->brand_id);
        $productId = $this->getCurrentProductId($brandId, $sku);
        $categoryId = $this->getCurrentCategoryId($categoryName);
        $stockId = $this->getCurrentStockId((int)$stockUnit->stock_id);

        return [
            'product_id' => $productId,
            'one_c_product_id' => (int)$stockUnit->one_c_product_id,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'stock_id' => $stockId,
            'sku' => $sku,
            'category_name' => $categoryName,
            'buy_price' => (float)$stockUnit->buy_price,
            'sell_price' => (float)$stockUnit->sell_price,
            'size_none' => (int)$stockUnit->size_none,
            'size_31' => (int)$stockUnit->size_31,
            'size_32' => (int)$stockUnit->size_32,
            'size_33' => (int)$stockUnit->size_33,
            'size_34' => (int)$stockUnit->size_34,
            'size_35' => (int)$stockUnit->size_35,
            'size_36' => (int)$stockUnit->size_36,
            'size_37' => (int)$stockUnit->size_37,
            'size_38' => (int)$stockUnit->size_38,
            'size_39' => (int)$stockUnit->size_39,
            'size_40' => (int)$stockUnit->size_40,
            'size_41' => (int)$stockUnit->size_41,
            'size_42' => (int)$stockUnit->size_42,
            'size_43' => (int)$stockUnit->size_43,
            'size_44' => (int)$stockUnit->size_44,
            'size_45' => (int)$stockUnit->size_45,
            'size_46' => (int)$stockUnit->size_46,
            'size_47' => (int)$stockUnit->size_47,
            'size_48' => (int)$stockUnit->size_48,
        ];
    }

    /**
     * Get current brand id by 1C brand id (code)
     */
    protected function getCurrentBrandId(int $brandId): ?int
    {
        return $this->brandIds[$brandId] ?? null;
    }

    /**
     * Get current product id by current brand id & 1C sku
     */
    protected function getCurrentProductId(?int $brandId, string $sku): ?int
    {
        if (!isset($this->productIds[$brandId])) {
            return null;
        }
        $brandProducts = $this->productIds[$brandId];
        $sku = $this->prepareSkuToCompare($sku);

        if (isset($brandProducts[$sku])) {
            return $brandProducts[$sku];
        }
        $wrongSkuRus = str_replace($this->engSymbols, $this->rusSymbols, $sku);
        if (isset($brandProducts[$wrongSkuRus])) {
            return $brandProducts[$wrongSkuRus];
        }
        $wrongSkuEng = str_replace($this->rusSymbols, $this->engSymbols, $sku);
        if (isset($brandProducts[$wrongSkuEng])) {
            return $brandProducts[$wrongSkuEng];
        }

        return null;
    }

    /**
     * Get current category id by 1C category name
     */
    protected function getCurrentCategoryId(string $categoryName): ?int
    {
        return $this->catagoryIds[$categoryName] ?? null;
    }

    /**
     * Get current stock id by 1C stock id (code)
     */
    protected function getCurrentStockId(int $stockId): ?int
    {
        return $this->stockIds[$stockId] ?? null;
    }

    /**
     * Filters the available sizes.
     *
     * This method filters the given values, excluding those with an empty SKU and categories
     * not specified in the configuration.
     */
    protected function filterAvailableSizes(array &$availableSizes): int
    {
        $count = 0;
        $excludeCategories = Config::findCacheable('inventory_blacklist')['categories'];

        foreach ($availableSizes as $key => $stock) {
            if (empty($stock['sku']) || in_array($stock['category_name'], $excludeCategories)) {
                unset($availableSizes[$key]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Update available sizes of products based on orders.
     */
    protected function updateAvailableSizesFromOrders(array &$availableSizes): int
    {
        $productsInOrders = [];
        $productsInOrdersDebug = [];
        $sizesCount = OrderItem::query()
            ->whereIn('status_key', ['new', 'reserved', 'confirmed', 'collect', 'pickup'])
            ->whereHas('statusLog', fn (Builder $query) => $query->whereNull('moved_at'))
            ->with('statusLog:order_item_id,stock_id')
            ->get(['id', 'product_id', 'size_id', 'count'])
            ->each(function (OrderItem $orderItem) use (&$productsInOrders, &$productsInOrdersDebug) {
                $productId = $orderItem->product_id;
                $sizeId = $orderItem->size_id;
                $stockId = $orderItem->statusLog->stock_id;
                $sizeCount = ($productsInOrders[$productId][$stockId][$sizeId] ?? 0) + $orderItem->count;
                $productsInOrders[$productId][$stockId][$sizeId] = $sizeCount;
                $productsInOrdersDebug[$orderItem->id][$productId][$stockId][$sizeId] = $sizeCount;
            })
            ->count();

        $this->debug('productsInOrdersDebug:', $productsInOrdersDebug);

        foreach ($availableSizes as &$stock) {
            if (empty($stock['product_id'])) {
                continue;
            }
            $productId = $stock['product_id'];
            $stockId = $stock['stock_id'];
            foreach ($productsInOrders[$productId][$stockId] ?? [] as $sizeId => &$count) {
                $sizeField = AvailableSizes::convertSizeIdToField($sizeId);
                $originalStockCount = $stock[$sizeField];

                $this->debug('subtract to order:', compact('stockId', 'productId', 'sizeField', 'count', 'originalStockCount'));

                if ($stock[$sizeField] - $count <= 0) {
                    $stock[$sizeField] = 0;
                } else {
                    $stock[$sizeField] -= $count;
                }
                $count -= $originalStockCount;
                if ($count <= 0) {
                    unset($productsInOrders[$productId][$stockId][$sizeId]);
                }
            }
        }

        return $sizesCount;
    }

    /**
     * Remove records where sum all sizes = 0
     */
    protected function removeEmptySizes(array &$availableSizes): int
    {
        $count = 0;
        $sizeFields = AvailableSizes::getSizeFields();
        foreach ($availableSizes as $key => $stock) {
            $sizes = Arr::only($stock, $sizeFields);
            if (array_sum($sizes) <= 0) {
                unset($availableSizes[$key]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Dispatch a job to notify offline orders based on available sizes data.
     */
    protected function notifyOfflineOrders(array $availableSizes): void
    {
        NotifyOfflineOrdersJob::dispatch($availableSizes);
    }
}
