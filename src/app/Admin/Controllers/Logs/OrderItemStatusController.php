<?php

namespace App\Admin\Controllers\Logs;

use App\Admin\Controllers\AbstractAdminController;
use App\Models\Logs\OrderItemStatusLog;
use App\Models\Orders\OrderItem;
use App\Models\Stock;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Row;
use Illuminate\Database\Eloquent\Builder;

class OrderItemStatusController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Лог движения единицы';

    /**
     * Stub for IDE
     */
    protected ?OrderItem $orderItem;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderItemStatusLog());

        $grid->column('created_at', 'Добавление в заказ');
        $grid->column('orderItem.product_id', 'Код товара');
        $grid->column('_brand', 'Бренд')->display(fn () => $this->orderItem?->product->brand->name);
        $grid->column('_sku', 'Артикул')->display(fn () => $this->orderItem?->product->sku);
        $grid->column('_size', 'Размер')->display(fn () => $this->orderItem?->size->name);
        $grid->column('orderItem.order_id', 'Заказ')->display(function (?int $orderId) {
            return $orderId ? "<a href='/admin/orders/$orderId/edit' target='_blank'>$orderId</a>" : null;
        });
        $grid->column('stock.internal_name', 'Склад');
        // $grid->column('reserved_at', __('Reserved at'));
        $grid->column('canceled_at', 'Отменен');
        $grid->column('confirmed_at', 'Подтвержден');
        $grid->column('collected_at', 'Собран');
        $grid->column('picked_up_at', 'Упаковано');
        $grid->column('moved_at', 'Перемещено на склад ИМ');
        $grid->column('sended_at', 'Отправлено');
        $grid->column('completed_at', 'Выкуплен');
        $grid->column('returned_at', 'Возврат');
        $grid->column('deleted_at', 'Нет в наличии');

        $grid->model()
            ->withTrashed()
            ->with(['orderItem.product', 'orderItem.size', 'stock.media'])
            ->orderBy('id', 'desc');
        // $grid->paginate(50);
        $grid->filter($this->getFilters());
        $grid->rows(fn (Row $row) => $row->column('_sku') ?: $row->style("background-color: #CCCCCC;"));
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Filters for grid
     */
    private function getFilters(): \Closure
    {
        return function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->where(function (Builder $query) {
                $query->whereHas('orderItem', function (Builder $query) {
                    $query->whereHas('product', function (Builder $query) {
                        $query->where('id', 'like', "%{$this->input}%")
                            ->orWhere('sku', 'like', "%{$this->input}%");
                    });
                });
            }, 'Код товара / артикул', 'product');

            $filter->where(function (Builder $query) {
                $query->whereHas('orderItem', function (Builder $query) {
                    $query->where('order_id', intval($this->input));
                });
            }, 'Номер заказа', 'order_id');

            $filter->in('stock_id', 'Склад')->multipleSelect(
                Stock::query()->where('check_availability', true)->pluck('internal_name', 'id')
            );
        };
    }
}
