<?php

namespace App\Admin\Controllers\Logs;

use App\Models\Logs\InventoryLog;
use App\Models\Size;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Show;

class InventoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'История автоматического обновления товаров';

    /**
     * Actions list
     */
    const ACTIONS = [
        InventoryLog::ACTION_RESTORE => 'Опубликован',
        InventoryLog::ACTION_UPDATE => 'Обновлен',
        InventoryLog::ACTION_DELETE => 'Снят с публикации',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new InventoryLog());

        $sizeNames = Size::pluck('name', 'id');
        $formatSizes = fn (?array $sizes) => $this->formatSizes($sizes, $sizeNames);
        $formatProduct = function (array $product) {
            return "{$product['category']['title']} {$product['brand']['name']} {$product['id']}";
        };

        $grid->column('product', 'Товар')->display($formatProduct);
        $grid->column('action', 'Действие')->using(self::ACTIONS);
        $grid->column('added_sizes', 'Добавленные размеры')->display($formatSizes);
        $grid->column('removed_sizes', 'Удаленные размеры')->display($formatSizes);
        $grid->column('created_at', 'Дата обновления');

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(50);

        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('product_id', 'Id продукта');
            $filter->equal('action', 'Действие')->select(self::ACTIONS);
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return back();
    }
}
