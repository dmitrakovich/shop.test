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
    const ONE_C_STOCK_QUANTITY_TABLE = 'SC6021';

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
            ])
            ->orderBy('ROW_ID')
            ->whereIn('SP5996', array_keys($this->stockIds))
            ->each(function (\stdClass $stockUnit) use (&$sizesAvailability) {
                dd($stockUnit);
                $sizesAvailability[] = dd([
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
                ]);
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
