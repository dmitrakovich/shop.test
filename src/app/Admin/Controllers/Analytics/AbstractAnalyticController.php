<?php

namespace App\Admin\Controllers\Analytics;

use App\Admin\Controllers\AbstractAdminController;
use App\Admin\Exports\AnalyticsExporter;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;

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
        $grid->column('total_purchased_price', 'Сумма выкупленных')->suffix('BYN', ' ');
        $grid->column('purchase_percentage', 'Процент выкупа')->display(function () {
            $purchased = (int)$this->getAttribute('purchased_count');
            $total = (int)$this->getAttribute('total_count');

            return $total ? round(($purchased / $total) * 100, 2) : 0;
        })->suffix('%', ' ');
        $grid->column('total_lost_price', 'Сумма потерянных')->suffix('BYN', ' ');

        $grid->expandFilter();
        $hasDefaultFilter = request()->has('default-filter');
        $grid->filter(function (Filter $filter) use ($hasDefaultFilter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Filter $filter) use ($hasDefaultFilter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '>=', $this->input);
                }, 'Начальная дата', 'order_created_at_start')
                    ->default($hasDefaultFilter ? now()->subDays(8)->startOfDay() : null)
                    ->datetime();
            });
            $filter->column(1 / 2, function (Filter $filter) use ($hasDefaultFilter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '<=', $this->input);
                }, 'Конечная дата', 'order_created_at_end')
                    ->default($hasDefaultFilter ? now()->subDays(1)->endOfDay() : null)
                    ->datetime();
            });
        });
        if ($hasDefaultFilter) {
            $this->applyDefaultFilter($grid);
        }

        $grid->exporter((new AnalyticsExporter())->setFileName($this->title));
        $grid->disablePagination();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Apply a default filter to the grid.
     */
    protected function applyDefaultFilter(Grid $grid): void
    {
        $values = [now()->subDays(8)->startOfDay(), now()->subDays(1)->endOfDay()];
        $grid->model()->whereBetween('orders.created_at', $values);
    }

    /**
     * Get the name of the database table associated with the analysis instance (Abstract Method).
     */
    abstract protected function getInstanceNameColumn(): string;

    /**
     * Get the column title for the instance (Abstract Method).
     */
    abstract protected function getInstanceColumnTitle(): string;

    /**
     * Get a prepared grid for analysis (Abstract Method).
     */
    abstract protected function getPreparedGrid(): Grid;
}