<?php

namespace App\Jobs;

use App\Jobs\Ssh\CreateTunnelJob;
use App\Jobs\Ssh\DestroyTunnelJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SizesAvailability;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class UpdateSizesAvailabilitiesTableJob extends AbstractJob
{
    /**
     * Table name in 1C, contains the quantity in stock
     */
    const ONE_C_STOCK_QUANTITY_TABLE = 'SC6046';

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * @var array
     */
    protected $contextVars = ['usedMemory'];

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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        return 'comming soon';


        $this->debug('Подготовка к синхронизации');
        $this->setCurrentStockIds();
        // $this->setCurrentProductIds();
        // $this->setCurrentBrandIds();
        // $this->setCurrentCategoryIds();

        $this->debug('Получение наличия с 1С');
        CreateTunnelJob::dispatchSync();

        $sizesAvailability = [];
        DB::connection('sqlsrv')
            ->table(self::ONE_C_STOCK_QUANTITY_TABLE)
            ->select([
                '' // перечислить нужные столбцы
            ])
            ->whereIn('ID', array_keys($this->stockIds))
            ->each(function (\stdClass $stockUnit) use (&$sizesAvailability) {
                $sizesAvailability[] = [
                    'product_id' => $stockUnit,
                    'one_c_product_id' => (int)$stockUnit,
                    'brand_id' => $stockUnit,
                    'category_id' => $stockUnit,
                    'stock_id' => $stockUnit,
                    'sku' => $stockUnit,
                    'buy_price' => (float)$stockUnit,
                    'sell_price' => (float)$stockUnit,
                    'size_none' => (int)$stockUnit,
                    'size_33' => (int)$stockUnit,
                    'size_34' => (int)$stockUnit,
                    'size_35' => (int)$stockUnit,
                    'size_36' => (int)$stockUnit,
                    'size_37' => (int)$stockUnit,
                    'size_38' => (int)$stockUnit,
                    'size_39' => (int)$stockUnit,
                    'size_40' => (int)$stockUnit,
                    'size_41' => (int)$stockUnit,
                ];
            });

        DestroyTunnelJob::dispatchSync();

        $this->complete('Запись полученных и сопоставленных данных в базу');
        $sizesAvailabilityTable = (new SizesAvailability)->getTable();
        DB::table($sizesAvailabilityTable)->truncate();
        DB::table($sizesAvailabilityTable)->insert($sizesAvailability);

        $this->complete('Наличие успешно обновлено');
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
}
