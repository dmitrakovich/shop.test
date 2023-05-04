<?php

namespace App\Admin\Controllers\Automation;

use App\Admin\Controllers\AbstractAdminController;
use App\Admin\Exports\StockExporter;
use App\Models\AvailableSizes;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Season;
use App\Models\Stock;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Row;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Склад';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AvailableSizes());

        $stockNames = Stock::query()->pluck('internal_name', 'id')->toArray();
        $brandNames = Brand::query()->pluck('name', 'id')->toArray();
        $defaultStockList = Stock::query()->where('type', 'shop')->pluck('id')->toArray();

        $select = [
            // 'GROUP_CONCAT(available_sizes.id SEPARATOR \',\') as stock_ids',
            'ANY_VALUE(products.id) as product_id',
            'ANY_VALUE(available_sizes.product_id) as available_sizes_product_id',


            'IFNULL(available_sizes.brand_id, products.brand_id) as brand_id',
            'IFNULL(available_sizes.category_id, products.category_id) as category_id ',
            'ANY_VALUE(available_sizes.id) as available_sizes_id',
            // 'GROUP_CONCAT(stocks.name SEPARATOR \', \') as stocks',
            'IFNULL(available_sizes.sku, products.sku) as sku',
            'MAX(available_sizes.buy_price) as buy_price', //!!!
            'MAX(available_sizes.sell_price) as sell_price', //!!!

            'MAX(products.price) as current_price',
            'MAX(products.old_price) as old_price',

            implode(', ', AvailableSizes::getSumWrappedSizeFields()),
        ];
        $unknownBrand = '<span class="text-red">неизветный бренд</span>';

        $grid->column('media', 'Фото')->display(fn () => $this->getFirstMediaUrl('default', 'thumb'))->image();
        $grid->column('product_id', 'product_id');
        $grid->column('available_sizes_id', 'available_sizes_id');
        // $grid->column('stock_ids', 'ids');
        $grid->column('brand.name', 'Бренд')->display(fn ($value) => $value ?: $unknownBrand);
        $grid->column('sku', 'Артикул');

        $stockIds = request()->input('stock_id') ?? $defaultStockList;
        foreach ($stockIds as $stockId) {
            $columnName = "stock_$stockId";
            $grid->column($columnName, $stockNames[$stockId]);
            $select[] = "'123' as $columnName"; // !!!
        }
        // dd($stockIds);

        $grid->column('Размеры')->display(fn () => $this->getFormatedSizes());
        $grid->column('buy_price', 'Цена покупки');
        $grid->column('sell_price', 'Цена продажи');


        $grid->column('current_price', 'current'); // !!!
        $grid->column('old_price', 'old'); // !!!

        $grid->model()->selectRaw(implode(', ', $select))
            ->leftJoin('products', 'products.id', '=', 'available_sizes.product_id')
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->union(
                DB::table('products')
                    ->selectRaw(implode(', ', $select))
                    ->leftJoin('available_sizes', 'available_sizes.product_id', '=', 'products.id')
                    ->whereNull('available_sizes.product_id')
                    ->groupBy(['sku', 'brand_id', 'category_id'])
            )
            ->orderBy('product_id', 'desc')
            ->with(['media']);

        $grid->rows($this->highlightRows());

        $grid->filter(function (Filter $filter) use ($stockNames, $brandNames, $defaultStockList) {
            $filter->disableIdFilter();
            $this->addProductFilter($filter);
            $this->addStockFilter($filter, $stockNames, $defaultStockList);
            $this->addStatusFilter($filter);
            $this->addBrandFilter($filter, $brandNames);
            $this->addSeasonFilter($filter);
            $this->addCollectionFilter($filter);
            $this->addCategoryFilter($filter);
        });

        $grid->exporter(new StockExporter());
        $grid->paginate(100);
        $grid->perPages([50, 100, 250, 500, 1000]);
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Adds a filter for products.
     */
    private function addProductFilter(Filter $filter): void
    {
        $filter->where(function (Builder $query) {
            $query->where('products.id', 'like', "%{$this->input}%")
                ->orWhere('products.sku', 'like', "%{$this->input}%")
                ->orWhere('available_sizes.sku', 'like', "%{$this->input}%");
        }, 'Код товара / артикул', 'product');
    }

    /**
     * Adds a filter for stocks.
     */
    private function addStockFilter(Filter $filter, array $stockList, array $defaultStockList): void
    {
        $filter->in('stock_id', 'Склад')->multipleSelect($stockList)->default($defaultStockList);
    }

    /**
     * Adds a filter for statuses.
     */
    private function addStatusFilter(Filter $filter): void
    {
        $statuses = [
            'discounts' => 'скидки',
            'new_items' => 'новинки',
            'out_of_stock' => 'нет в наличии',
            'not_added' => 'не выставлено',
        ];

        $queryCallback = function (Builder $query) {
            foreach ($this->input as $input) {
                //todo: если получится коротко, то переделать на match
                switch ($input) {
                    case 'discounts':
                        $query->whereColumn('products.old_price', '>', 'products.price');
                        break;
                    case 'new_items':
                        $query->doesntHave('somerelationship');
                        break;
                    case 'out_of_stock':
                        $query->doesntHave('somerelationship');
                        break;
                    case 'not_added':
                        $query->doesntHave('somerelationship');
                        break;
                }
            }
        };

        $filter->where($queryCallback, 'Статус', 'status')->checkbox($statuses);
    }

    /**
     * Adds a filter for brands.
     */
    private function addBrandFilter(Filter $filter, array $brandList): void
    {
        $queryCallback = function (Builder $query) {
            $query->whereIn('available_sizes.brand_id', $this->input)
                ->orWhereIn('products.brand_id', $this->input);
        };

        $filter->where($queryCallback, 'Бренд', 'brand')->multipleSelect($brandList);
    }

    /**
     * Adds a filter for seasons.
     */
    private function addSeasonFilter(Filter $filter): void
    {
        $queryCallback = fn (Builder $query) => $query->whereIn('products.season_id', $this->input);

        $filter->where($queryCallback, 'Сезон', 'season')->multipleSelect(Season::pluck('name', 'id'));
    }

    /**
     * Adds a filter for collections.
     */
    private function addCollectionFilter(Filter $filter): void
    {
        $queryCallback = fn (Builder $query) => $query->whereIn('products.collection_id', $this->input);

        $filter->where($queryCallback, 'Коллекция', 'collection')->multipleSelect(Collection::pluck('name', 'id'));
    }

    /**
     * Adds a filter for categories.
     */
    private function addCategoryFilter(Filter $filter): void
    {
        $queryCallback = function (Builder $query) {
            $query->whereIn('available_sizes.category_id', $this->input)
                ->orWhereIn('products.category_id', $this->input);
        };

        $filter->where($queryCallback, 'Категория', 'category')->multipleSelect(Category::getFormatedTree());
    }

    /**
     * Highlight rows in a table.
     */
    function highlightRows(): \Closure
    {
        $yellow = 'rgba(255, 255, 0, 0.3)';
        $red = 'rgba(255, 0, 0, 0.3)';
        $turquoise = 'rgba(64, 224, 208, 0.3)';

        return function (Row $row) use ($yellow, $red, $turquoise) {
            $isInCatalogue = !empty($row->column('product_id'));
            $isInStock = !empty($row->column('available_sizes_product_id'));
            $oldPrice = (float)$row->column('old_price');
            $currentPrice = (float)$row->column('current_price');

            if (!$isInCatalogue) {
                $row->style("background-color: $turquoise;");
            } elseif ($isInCatalogue && !$isInStock) {
                $row->style("background-color: $red;");
            } elseif ($oldPrice > $currentPrice) {
                $row->style("background-color: $yellow;");
            }
        };
    }
}
