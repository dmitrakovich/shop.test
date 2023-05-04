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
     * List of status filters
     */
    const statusfilters = [
        'discounts' => 'скидки',
        'new_items' => 'новинки',
        'out_of_stock' => 'нет в наличии',
        'not_added' => 'не выставлено',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AvailableSizes());

        $stockNames = Stock::query()->pluck('internal_name', 'id')->toArray();
        $defaultStockList = Stock::query()->where('type', 'shop')->pluck('id')->toArray();
        $select = [
            'ANY_VALUE(products.id) as product_id',
            'ANY_VALUE(available_sizes.product_id) as available_sizes_product_id',
            'IFNULL(available_sizes.brand_id, products.brand_id) as brand_id',
            'IFNULL(available_sizes.category_id, products.category_id) as category_id ',
            'IFNULL(available_sizes.sku, products.sku) as sku',
            'MAX(available_sizes.sell_price) as sell_price',
            'MAX(products.price) as current_price',
            'MAX(products.old_price) as old_price',
            implode(', ', AvailableSizes::getGroupConcatWrappedSizeFields()),
        ];

        $grid->column('media', 'Фото')->display(fn () => $this->getFirstMediaUrl('default', 'thumb'))->image();
        $grid->column('product_name', 'Название')->display(fn () => $this->getNameForStock());

        $stockIds = request()->input('stock_id') ?? $defaultStockList;
        foreach ($stockIds as $stockId) {
            $columnName = "stock_$stockId";
            $grid->column($columnName, $stockNames[$stockId])->display(fn () => $this->getFormattedSizesForStock($columnName));
            $select[] = "GROUP_CONCAT(available_sizes.stock_id) as $columnName";
        }
        $grid->column('sizes.name', 'размеры на сайте')->display(fn () => $this->sizes->map(fn ($size) => $size->name)->implode(', '));
        $grid->column('sell_price', 'цена в 1С');
        $grid->column('current_price', 'цена на сайте');
        $grid->column('discount', 'скидка')->display(fn () => $this->getFormatedDiscountForStock());

        $grid->model()->selectRaw(implode(', ', $select))
            ->leftJoin('products', 'products.id', '=', 'available_sizes.product_id')
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->union(
                DB::table('products')
                    ->selectRaw(implode(', ', $select))
                    ->leftJoin('available_sizes', 'available_sizes.product_id', '=', 'products.id')
                    ->where($this->addFiltersForProducts())
                    ->whereNull('available_sizes.product_id')
                    ->groupBy(['sku', 'brand_id', 'category_id'])
            )
            ->orderBy('product_id', 'desc')
            ->with(['media', 'brand:id,name', 'sizes:id,name']);

        $grid->rows($this->highlightRows());
        $grid->filter(fn (Filter $filter) => $this->addFiltersForAvailableSizes($filter, $stockNames, $defaultStockList));
        $grid->exporter(new StockExporter());
        $grid->paginate(100);
        $grid->perPages([50, 100, 250, 500, 1000]);
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }


    private function addFiltersForAvailableSizes(Filter $filter, array $stockNames, array $defaultStockList): void
    {
        $filter->disableIdFilter();
        //? какой-той еще фильтр
        $filter->where($this->getProductFilter(), 'Код товара / артикул', 'product');
        $filter->in('stock_id', 'Склад')->multipleSelect($stockNames)->default($defaultStockList);
        $filter->where($this->getStatusFilter(), 'Статус', 'status')->checkbox(self::statusfilters);
        $filter->where($this->getBrandFilter(), 'Бренд', 'brand')->multipleSelect(Brand::pluck('name', 'id'));
        $filter->where($this->getSeasonFilter(), 'Сезон', 'season')->multipleSelect(Season::pluck('name', 'id'));
        $filter->where($this->getCollectionFilter(), 'Коллекция', 'collection')->multipleSelect(Collection::pluck('name', 'id'));
        $filter->where($this->getCategoryFilter(), 'Категория', 'category')->multipleSelect(Category::getFormatedTree());
    }

    private function addFiltersForProducts(): \Closure
    {
        return function ($query) {
            if (!empty($productQuery = request('product'))) {
                $query->where($this->getProductFilter('products', $productQuery));
            }
            if (!empty($statuses = request('status'))) {
                $query->where($this->getStatusFilter((array)$statuses));
            }
            if (!empty($brandQuery = request('brand'))) {
                $query->where($this->getBrandFilter('products', (array)$brandQuery));
            }
            if (!empty($seasonQuery = request('season'))) {
                $query->where($this->getSeasonFilter((array)$seasonQuery));
            }
            if (!empty($collectionQuery = request('collection'))) {
                $query->where($this->getCollectionFilter((array)$collectionQuery));
            }
            if (!empty($categoryQuery = request('category'))) {
                $query->where($this->getCategoryFilter('products', (array)$categoryQuery));
            }
        };
    }

    /**
     * Adds a filter for products.
     */
    private function getProductFilter(string $table = 'available_sizes', ?string $input = null): \Closure
    {
        return function ($query) use ($table, $input) {
            $input ??= $this->input;
            $query->where('products.id', 'like', "%{$input}%")
                ->orWhere("$table.sku", 'like', "%{$input}%");
        };
    }

    /**
     * Adds a filter for statuses.
     */
    private function getStatusFilter(?array $input = null): \Closure
    {
        return function ($query) use ($input) {
            $statuses = $input ?? $this->input;
            foreach ($statuses as $status) {
                match ($status) {
                    'discounts' => $query->whereColumn('products.old_price', '>', 'products.price'),
                    'new_items' => $query->where('products.old_price', 0),
                    'out_of_stock' => $query->whereNotNull('products.deleted_at'),
                    'not_added' => $query->whereNull('products.id')->whereNull('available_sizes.product_id'),
                };
            }
        };
    }

    /**
     * Adds a filter for brands.
     */
    private function getBrandFilter(string $table = 'available_sizes', ?array $input = null): \Closure
    {
        return fn ($query) => $query->whereIn("$table.brand_id", $input ?? $this->input);
    }

    /**
     * Adds a filter for seasons.
     */
    private function getSeasonFilter(?array $input = null): \Closure
    {
        return fn ($query) => $query->whereIn('products.season_id', $input ?? $this->input);
    }

    /**
     * Adds a filter for collections.
     */
    private function getCollectionFilter(?array $input = null): \Closure
    {
        return fn ($query) => $query->whereIn('products.collection_id', $input ?? $this->input);
    }

    /**
     * Adds a filter for categories.
     */
    private function getCategoryFilter(string $table = 'available_sizes', ?array $input = null): \Closure
    {
        return fn ($query) => $query->whereIn("$table.category_id", $input ?? $this->input);
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
