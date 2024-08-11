<?php

namespace App\Admin\Controllers\Automation;

use App\Admin\Controllers\AbstractAdminController;
use App\Models\AvailableSizes;
use Encore\Admin\Grid;

/**
 * @mixin AvailableSizes
 */
class InventoryController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Товары которые необходимо добавить';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AvailableSizes);

        $select = [
            'GROUP_CONCAT(available_sizes.id SEPARATOR \',\') as stock_ids',
            'brand_id',
            'category_id',
            'MAX(category_name) as category_name',
            // 'GROUP_CONCAT(stocks.name SEPARATOR \', \') as stocks',
            'sku',
            'MAX(buy_price) as buy_price',
            'MAX(sell_price) as sell_price',
            implode(', ', AvailableSizes::getSumWrappedSizeFields()),
        ];

        $grid->model()->selectRaw(implode(', ', $select))
            // ->join('stocks', 'stocks.id', '=', 'available_sizes.stock_id')
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->whereNull('product_id');

        $unknownCategory = '<span class="text-red">неизветная категория</span>';
        $unknownBrand = '<span class="text-red">неизветный бренд</span>';

        $grid->column('category.title', 'Категория')->display(fn ($value) => $value ?: $unknownCategory);
        $grid->column('category_name', 'Категория с 1С');
        $grid->column('brand.name', 'Бренд')->display(fn ($value) => $value ?: $unknownBrand);
        $grid->column('sku', 'Артикул');
        // $grid->column('stocks', 'Склады');
        $grid->column('Размеры')->display(fn () => $this->getFormattedSizes());
        $grid->column('buy_price', 'Цена покупки');
        $grid->column('sell_price', 'Цена продажи');
        $grid->column('stock_ids', 'Опции')->display(function (string $stockIds) {
            $link = route('admin.products.create', ['stock_ids' => $stockIds]);

            return '<a href="' . $link . '" class="btn btn-xs btn-primary" target="_blank">Создать</a>';
        });

        $grid->paginate(50);
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }
}
