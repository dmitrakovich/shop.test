<?php

namespace App\Admin\Controllers\Orders;

use App\Admin\Controllers\AbstractAdminController;
use App\Enums\StockTypeEnum;
use App\Models\Orders\OfflineOrder;
use App\Models\Stock;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;

/**
 * @mixin OfflineOrder
 */
class OfflineOrderController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Оффлайн заказы';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OfflineOrder);

        $grid->column('receipt_number', 'Номер чека');
        $grid->column('product_photo', 'Превью')->display(fn () => $this->product?->getFirstMediaUrl('default', 'thumb'))->image();
        $grid->column('product_id', 'Код товара');
        $grid->column('sku', 'Артикул в 1С');
        $grid->column('size.name', 'Размер');
        $grid->column('price', 'Сумма')->suffix('BYN', ' ');
        $grid->column('sold_at', 'Дата')->display(fn ($datetime) => self::formatDateTime($datetime));
        $grid->column('stock.internal_name', 'Магазин');
        $grid->column('user', 'Клиент')->display(function (?array $user) {
            return $user ? '<a href="' . route('admin.users.edit', $user['id']) . '" target="_blank">' . $this->user->getFullName() . '</a>' : null;
        });
        $grid->column('user_phone', 'Телефон');
        $grid->column('returned_at', 'Дата возврата')->display(fn ($datetime) => self::formatDateTime($datetime));

        $grid->model()->with(['product.media'])->orderBy('id', 'desc');
        $grid->disableActions();
        $grid->paginate(50);

        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->between('sold_at', 'Дата')->datetime();
            $filter->equal('stock_id', 'Магазин')->select(Stock::query()->where('type', StockTypeEnum::SHOP)->pluck('address', 'id'));
            $filter->like('user_phone', 'Телефон');
            $filter->where(function ($query) {
                $query->where('product_id', 'like', "%{$this->input}%")
                    ->orWhere('sku', 'like', "%{$this->input}%");
            }, 'Код товара / артикул');
        });

        return $grid;
    }
}
