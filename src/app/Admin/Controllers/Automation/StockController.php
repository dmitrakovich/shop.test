<?php

namespace App\Admin\Controllers\Automation;

use App\Admin\Controllers\AbstractAdminController;
use App\Admin\Exports\StockExporter;
use App\Admin\Models\AvailableSizesFull;
use App\Admin\Tools\UpdateAvailability;
use App\Jobs\AvailableSizes\UpdateAvailableSizesFullTableJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductAttributes\CountryOfOrigin;
use App\Models\Season;
use App\Models\Stock;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Row;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @mixin AvailableSizesFull
 * @phpstan-require-extends AvailableSizesFull
 */
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
    const statusFilters = [
        'discounts' => 'скидки',
        'new_items' => 'новинки',
        'out_of_stock' => 'продано',
        'excluded' => 'исключенные',
        'not_added' => 'не выставлено',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AvailableSizesFull());

        $stockNames = Stock::query()->pluck('internal_name', 'id')->toArray();
        $defaultStockList = Stock::query()->where('type', 'shop')->pluck('id')->toArray();
        $select = [
            'ANY_VALUE(products.id) as product_id',
            'ANY_VALUE(available_sizes_full.product_id) as available_sizes_product_id',
            'ANY_VALUE(products.label_id) as label_id',
            'IFNULL(available_sizes_full.brand_id, products.brand_id) as brand_id',
            'IFNULL(available_sizes_full.category_id, products.category_id) as category_id ',
            'IFNULL(available_sizes_full.sku, products.sku) as sku',
            'MAX(available_sizes_full.sell_price) as sell_price',
            'MAX(products.price) as current_price',
            'MAX(products.old_price) as old_price',
            implode(', ', AvailableSizesFull::getGroupConcatWrappedSizeFields()),
        ];

        $grid->column('media', 'Фото')->display(fn () => $this->getFirstMediaUrl('default', 'thumb'))->image();
        $grid->column('product_name', 'Название')->display(fn () => $this->getNameForStock());

        $stockIds = request()->input('stock_id') ?? $defaultStockList;
        foreach ($stockIds as $stockId) {
            $columnName = "stock_$stockId";
            $grid->column($columnName, $stockNames[$stockId])->display(fn () => $this->getFormattedSizesForStock($columnName));
            $select[] = "GROUP_CONCAT(available_sizes_full.stock_id) as $columnName";
        }
        $grid->column('sizes.name', 'размеры на сайте')->display(fn () => $this->sizes->map(fn ($size) => $size->name)->implode(', '));
        $grid->column('sell_price', 'цена в 1С');
        $grid->column('current_price', 'цена на сайте');
        $grid->column('discount', 'скидка')->display(fn () => self::getFormattedDiscountForStock($this));

        $maxSizesCountFilter = request()->integer('max_sizes_count');
        $showAll = request('show') !== 'only_in_stock' && !$maxSizesCountFilter;

        $grid->model()->selectRaw(implode(', ', $select))
            ->leftJoin('products', 'products.id', '=', 'available_sizes_full.product_id')
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->when($maxSizesCountFilter, function ($query) use ($maxSizesCountFilter) {
                $availableSizes = array_map(fn (string $size) => "SUM($size)", AvailableSizesFull::getSizeFields());
                $availableSizesCount = implode(' + ', $availableSizes);
                $query->havingRaw("($availableSizesCount) <= $maxSizesCountFilter");
            })
            ->when($showAll, function ($query) use ($select) {
                return $query->union(
                    DB::table('products')
                        ->selectRaw(implode(', ', $select))
                        ->leftJoin('available_sizes_full', 'available_sizes_full.product_id', '=', 'products.id')
                        ->where($this->addFiltersForProducts())
                        ->whereNull('available_sizes_full.product_id')
                        ->groupBy(['sku', 'brand_id', 'category_id'])
                );
            })
            ->orderBy('product_id', 'desc')
            ->with(['media', 'brand:id,name', 'sizes:id,name']);

        $grid->rows($this->highlightRows());
        $grid->filter(fn (Filter $filter) => $this->addFiltersForAvailableSizes($filter, $stockNames, $defaultStockList, $maxSizesCountFilter));
        $grid->tools(fn ($tools) => $tools->append(new UpdateAvailability()));
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
     * Get formatted discount percentage for the product.
     */
    private static function getFormattedDiscountForStock($stockObject): ?string
    {
        if (empty($sellPrice = (float)$stockObject->sell_price) || empty($currentPrice = (float)$stockObject->current_price)) {
            return null;
        }

        return round((($sellPrice - $currentPrice) / $sellPrice) * 100, 2) . '%';
    }

    private function addFiltersForAvailableSizes(Filter $filter, array $stockNames, array $defaultStockList, int $maxSizesCountFilter): void
    {
        $filter->disableIdFilter();
        if ($maxSizesCountFilter) {
            $filter->where(fn ($query) => $query, 'Товары, которых нет на складе', 'show_only_in_stock')->radio(['only_in_stock' => 'скрыть'])->default('only_in_stock');
        } else {
            $filter->where(fn ($query) => $query, 'Товары, которых нет на складе', 'show')->radio(['all' => 'показывать', 'only_in_stock' => 'скрыть'])->default('all');
        }
        $filter->where($this->getProductFilter(), 'Код товара / артикул', 'product');
        $filter->in('stock_id', 'Склад')->checkbox($stockNames)->default($defaultStockList);
        $filter->where($this->getStatusFilter(), 'Статус', 'status')->checkbox(self::statusFilters);
        $filter->where($this->getBrandFilter(), 'Бренд', 'brand')->multipleSelect(Brand::pluck('name', 'id'));
        $filter->where($this->getSeasonFilter(), 'Сезон', 'season')->multipleSelect(Season::pluck('name', 'id'));
        $filter->where($this->getCollectionFilter(), 'Коллекция', 'collection')->multipleSelect(Collection::pluck('name', 'id'));
        $filter->where($this->getCategoryFilter(), 'Категория', 'category')->multipleSelect(Category::getFormatedTree());
        $filter->where($this->getCountryOfOriginFilter(), 'Страна производитель', 'country_of_origin')->multipleSelect(CountryOfOrigin::pluck('name', 'id'));
        $filter->where(fn ($query) => $query, 'Макс. кол-во пар на модель', 'max_sizes_count')->placeholder('Введите кол-во ед.');
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
            if (!empty($countryOfOriginQuery = request('country_of_origin'))) {
                $query->where($this->getCountryOfOriginFilter((array)$countryOfOriginQuery));
            }
        };
    }

    /**
     * Adds a filter for products.
     */
    private function getProductFilter(string $table = 'available_sizes_full', ?string $input = null): \Closure
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
                    'out_of_stock' => $query->whereNull('available_sizes_full.product_id'),
                    'excluded' => $query->whereIn('products.label_id', Product::excludedLabels()),
                    'not_added' => $query->whereNull('products.id')->whereNull('available_sizes_full.product_id'),
                };
            }
        };
    }

    /**
     * Adds a filter for brands.
     */
    private function getBrandFilter(string $table = 'available_sizes_full', ?array $input = null): \Closure
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
    private function getCategoryFilter(string $table = 'available_sizes_full', ?array $input = null): \Closure
    {
        return function ($query) use ($table, $input) {
            $categories = [];
            foreach ($input ?? $this->input ?? [] as $categoryId) {
                $categories = array_merge($categories, Category::getChildrenCategoriesIdsList($categoryId));
            }

            return $query->whereIn("$table.category_id", $categories);
        };
    }

    /**
     * Adds a filter for countryOfOrigin.
     */
    private function getCountryOfOriginFilter(?array $input = null): \Closure
    {
        return fn ($query) => $query->whereIn('products.country_of_origin_id', $input ?? $this->input);
    }

    /**
     * Highlight rows in a table.
     */
    public function highlightRows(): \Closure
    {
        $yellow = '#FFFFB2';
        $red = '#FFB2B2';
        $turquoise = '#C5F6F1';
        $gray = '#CCCCCC';

        return function (Row $row) use ($yellow, $red, $turquoise, $gray) {
            $isInCatalogue = !empty($row->column('product_id'));
            $isInStock = !empty($row->column('available_sizes_product_id'));
            $isExcluded = in_array((int)$row->column('label_id'), Product::excludedLabels());
            $oldPrice = (float)$row->column('old_price');
            $currentPrice = (float)$row->column('current_price');

            if (!$isInCatalogue) {
                $row->style("background-color: $turquoise;");
            } elseif ($isInCatalogue && $isExcluded) {
                $row->style("background-color: $gray;");
            } elseif ($isInCatalogue && !$isInStock) {
                $row->style("background-color: $red;");
            } elseif ($oldPrice > $currentPrice) {
                $row->style("background-color: $yellow;");
            }
        };
    }

    /**
     * Run UpdateAvailableSizesFullTableJob
     */
    public function updateAvailability(): RedirectResponse
    {
        dispatch_sync(new UpdateAvailableSizesFullTableJob());

        Cache::forever('available_sizes_full_last_update', now()->format('d.m H:i:s'));

        return back();
    }
}
