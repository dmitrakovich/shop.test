<?php

namespace App\Admin\Controllers\Analytics;

use App\Admin\Controllers\AbstractAdminController;
use App\Admin\Exports\AnalyticsExporter;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class AbstractAnalyticController extends AbstractAdminController
{
    /**
     * Mapping of custom order statuses to their corresponding database values.
     */
    protected array $statuses = [
        'accepted' => "'new'",
        'in_progress' => "'in_work', 'wait_payment', 'paid', 'assembled', 'packaging', 'ready', 'sent', 'fitting', 'confirmed'",
        'purchased' => "'complete', 'installment', 'partial_complete'",
        'canceled' => "'canceled'",
        'returned' => "'return', 'return_fitting'",
        'lost' => "'canceled', 'return', 'return_fitting'",
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = $this->getPreparedGrid();

        $grid->column('instance_name', $this->getInstanceColumnTitle())->default('Неопределено');
        $grid->column('total_count', 'Все');
        $grid->column('accepted_count', 'Принят');
        $grid->column('in_progress_count', 'В работе');
        $grid->column('purchased_count', 'Выкуплен');
        $grid->column('canceled_count', 'Отменен');
        $grid->column('returned_count', 'Возврат');
        $grid->column('total_purchased_price', 'Сумма выкупленных')->suffix('BYN');
        $grid->column('purchase_percentage', 'Процент выкупа')->suffix('%');
        $grid->column('total_lost_price', 'Сумма потерянных')->suffix('BYN');

        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->column(1/2, function (Filter $filter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '>=', $this->input);
                }, 'Начальная дата', 'order_created_at_start')->datetime();
            });
            $filter->column(1/2, function (Filter $filter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '<=', $this->input);
                }, 'Конечная дата', 'order_created_at_end')->datetime();
            });
        });

        $grid->exporter((new AnalyticsExporter())->setFileName($this->title));
        $grid->disablePagination();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Get a query to retrieve the last order ID for each user.
     */
    protected function getLastUserOrdersQuery() : Builder
    {
        return DB::table('users')
            ->select(['users.id as user_id', DB::raw('MAX(orders.id) AS order_id')])
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->whereNotNull('orders.id')
            ->groupBy('users.id');
    }

    /**
     * Get the column title for the instance (Abstract Method).
     */
    abstract protected function getInstanceColumnTitle(): string;

    /**
     * Get a prepared grid for analysis (Abstract Method).
     */
    abstract protected function getPreparedGrid(): Grid;
}
