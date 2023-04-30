<?php

namespace App\Admin\Controllers\Automation;

use App\Admin\Controllers\AbstractAdminController;
use App\Models\AvailableSizes;
use Encore\Admin\Grid;

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
        $grid = new Grid(new AvailableSizes());

        $select = [
            'GROUP_CONCAT(available_sizes.id SEPARATOR \',\') as stock_ids',
            'brand_id',
            'category_id',
            // 'GROUP_CONCAT(stocks.name SEPARATOR \', \') as stocks',
            'sku',
            'MAX(buy_price) as buy_price',
            'MAX(sell_price) as sell_price',
            implode(', ', AvailableSizes::getSumWrappedSizeFields()),
        ];

        $grid->model()->selectRaw(implode(', ', $select))
            // ->join('stocks', 'stocks.id', '=', 'available_sizes.stock_id')
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->whereNull('product_id')
            ->where('sku', '!=', ''); //TODO на этапе создания таблицы отсеивать такие значения!!!

        $unknownCategory = '<span class="text-red">неизветная категория</span>';
        $unknownBrand = '<span class="text-red">неизветный бренд</span>';

        $grid->column('category.title', 'Категория')->display(fn ($value) => $value ?: $unknownCategory);
        $grid->column('brand.name', 'Бренд')->display(fn ($value) => $value ?: $unknownBrand);
        $grid->column('sku', 'Артикул');
        // $grid->column('stocks', 'Склады');
        $grid->column('Размеры')->display(fn () => $this->getFormatedSizes());
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
