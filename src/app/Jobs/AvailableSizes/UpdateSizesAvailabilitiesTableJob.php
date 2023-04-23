<?php

namespace App\Jobs\AvailableSizes;

use App\Jobs\Ssh\CreateTunnelJob;
use App\Jobs\Ssh\DestroyTunnelJob;
use App\Models\AvailableSizes;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class UpdateSizesAvailabilitiesTableJob extends AbstractAvailableSizesJob
{
    /**
     * Table name in 1C, contains the quantity in stock
     */
    const ONE_C_STOCK_QUANTITY_TABLE = 'SC6021';

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
        CreateTunnelJob::dispatchSync();

        // TODO: отфильтровать пустые артикулы

        $availableSizes = [];
        DB::connection('sqlsrv')
            ->table(self::ONE_C_STOCK_QUANTITY_TABLE)
            ->select($this->getStockQuantityFields())
            ->orderBy('ROW_ID')
            ->whereIn('SP5996', array_keys($this->stockIds))
            ->each(function (\stdClass $stockUnit) use (&$availableSizes) {
                $availableSizes[] = $this->prepareAvailableSizesData($stockUnit);
            });

        DestroyTunnelJob::dispatchSync();

        $this->log('Запись полученных и сопоставленных данных в базу');
        $availableSizesTable = (new AvailableSizes())->getTable();
        DB::table($availableSizesTable)->truncate();
        DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        DB::table($availableSizesTable)->insert($availableSizes);
        DB::connection()->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

        $this->log('Таблица с наличием успешно обновлена');
    }

    /**
     * List of 1C stock quantity table's fields
     */
    protected function getStockQuantityFields(): array
    {
        return [
            'ROW_ID',
            'SP6022 as one_c_product_id',
            'SP5998 as brand_id',
            'DESCR as category_name',
            'SP5996 as stock_id',
            'SP5993 as sku',
            'SP6000 as buy_price',
            'SP5999 as sell_price',
            'SP6001 as size_none',
            'SP6002 as size_31',
            'SP6003 as size_32',
            'SP6004 as size_33',
            'SP6005 as size_34',
            'SP6006 as size_35',
            'SP6007 as size_36',
            'SP6008 as size_37',
            'SP6009 as size_38',
            'SP6010 as size_39',
            'SP6011 as size_40',
            'SP6012 as size_41',
            'SP6013 as size_42',
            'SP6014 as size_43',
            'SP6015 as size_44',
            'SP6016 as size_45',
            'SP6017 as size_46',
            'SP6018 as size_47',
            'SP6019 as size_48',
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
        $brandId = $this->getCurrentBrandId((int)$stockUnit->brand_id);
        $productId = $this->getCurrentProductId($brandId, $sku);
        $categoryId = $this->getCurrentCategoryId($stockUnit->category_name);
        $stockId = $this->getCurrentStockId((int)$stockUnit->stock_id);

        return [
            'product_id' => $productId,
            'one_c_product_id' => (int)$stockUnit->one_c_product_id,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'stock_id' => $stockId,
            'sku' => $sku,
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
        return $this->catagoryIds[trim($categoryName)] ?? null;
    }

    /**
     * Get current stock id by 1C stock id (code)
     */
    protected function getCurrentStockId(int $stockId): ?int
    {
        return $this->stockIds[$stockId] ?? null;
    }
}
