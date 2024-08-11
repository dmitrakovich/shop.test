<?php

namespace App\Admin\Controllers\OrdersDistribution;

use App\Admin\Controllers\AbstractAdminController;
use App\Admin\Exports\AnalyticsExporter;
use App\Enums\Order\OrderTypeEnum;
use App\Models\Logs\OrderDistributionLog;
use App\Models\Orders\Order;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;

/**
 * @mixin Order
 */
class StatisticController extends AbstractAdminController
{
    protected $title = 'Статистика распределения';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);
        $orderCreatedAtStart = request()->input('order_created_at_start');
        $orderCreatedAtEnd = request()->input('order_created_at_end');
        $filterDateValues = [
            $orderCreatedAtStart ? $orderCreatedAtStart : now()->startOfDay(),
            $orderCreatedAtEnd ? $orderCreatedAtEnd : now()->endOfDay(),
        ];
        $orderTypeManager = OrderTypeEnum::MANAGER->value;
        $totalDistribution = OrderDistributionLog::whereBetween('created_at', $filterDateValues)->count();
        $select = <<<SQL
            CONCAT(admin_users.user_last_name, ' ', SUBSTRING(admin_users.name, 1, 1), '.') AS instance_name,
            COUNT(DISTINCT log_order_distribution.id) AS distribution_count,
            ROUND((COUNT(DISTINCT log_order_distribution.id) / {$totalDistribution} * 100), 2) AS distribution_percentage,
            COUNT(DISTINCT CASE WHEN (orders.order_type = '{$orderTypeManager}') THEN orders.id ELSE null END) AS created_manually,
            (COUNT(DISTINCT orders.id) - COUNT(DISTINCT log_order_distribution.id)) AS accepted_manually,
            COUNT(DISTINCT orders.id) AS total_count
        SQL;
        $grid->model()
            ->selectRaw($select)
            ->leftJoin('log_order_distribution', 'orders.id', '=', 'log_order_distribution.order_id')
            ->leftJoin('admin_users', 'orders.admin_id', '=', 'admin_users.id')
            ->whereBetween('orders.created_at', $filterDateValues)
            ->groupBy('admin_users.id');

        $grid->column('instance_name', 'Менеджер')->default('Неопределено');
        $grid->column('distribution_count', 'Заказов распределено');
        $grid->column('distribution_percentage', '% от всех распределенных');
        $grid->column('created_manually', 'Создано вручную');
        $grid->column('accepted_manually', 'Принято вручную');
        $grid->column('total_count', 'Всего заказов (по дате создания)');

        $grid->expandFilter();
        $grid->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function (Filter $filter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '>=', $this->input);
                }, 'Начальная дата', 'order_created_at_start')
                    ->default(now()->startOfDay())
                    ->datetime();
            });
            $filter->column(1 / 2, function (Filter $filter) {
                $filter->where(function ($query) {
                    return $query->where('orders.created_at', '<=', $this->input);
                }, 'Конечная дата', 'order_created_at_end')
                    ->default(now()->endOfDay())
                    ->datetime();
            });
        });

        $grid->exporter((new AnalyticsExporter)->setFileName($this->title));
        $grid->disablePagination();
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }
}
