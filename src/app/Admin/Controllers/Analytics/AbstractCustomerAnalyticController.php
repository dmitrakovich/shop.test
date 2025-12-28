<?php

namespace App\Admin\Controllers\Analytics;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class AbstractCustomerAnalyticController extends AbstractAnalyticController
{
    /**
     * Get the SQL SELECT statement for the analysis.
     */
    protected function getSelectSql(): string
    {
        return <<<SQL
            {$this->getInstanceNameColumn()} AS instance_name,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where purchased_count >= 1)) THEN orders.user_id ELSE null END) AS purchased_count,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where progress_count >= 1 and purchased_count = 0)) THEN orders.user_id ELSE null END) AS in_progress_count,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where returned_count >= 1 and purchased_count = 0 and progress_count = 0)) THEN orders.user_id ELSE null END) AS returned_count,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where canceled_count >= 1 and purchased_count = 0 and progress_count = 0 and returned_count = 0)) THEN orders.user_id ELSE null END) AS canceled_count,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where accepted_count >= 1 and purchased_count = 0 and progress_count = 0 and returned_count = 0 and canceled_count = 0)) THEN orders.user_id ELSE null END) AS accepted_count,
            COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount)) THEN orders.user_id ELSE null END) AS total_count,
            ROUND(SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('purchased')}) AND order_items.status_key IN ({$this->getStatusesForQuery('purchased')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_purchased_price,
            ROUND(SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('lost')}) AND order_items.status_key IN ({$this->getStatusesForQuery('lost')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_lost_price
        SQL;
    }

    /**
     * Get a query to retrieve the last order ID for each user.
     */
    protected function getUserOrderStatusCountQuery(): Builder
    {
        $defaultFilter = request()->input('default_filter');
        $orderCreatedAtStart = $defaultFilter ? now()->subDays(8)->startOfDay() : request()->input('order_created_at_start');
        $orderCreatedAtEnd = $defaultFilter ? now()->subDays(1)->endOfDay() : request()->input('order_created_at_end');
        $selectRaw = <<<SQL
            users.id as user_id,
            SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('purchased')}) THEN 1 ELSE 0 END) as purchased_count,
            SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('accepted')}) THEN 1 ELSE 0 END) as accepted_count,
            SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('in_progress')}) THEN 1 ELSE 0 END) as progress_count,
            SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('canceled')}) THEN 1 ELSE 0 END) as canceled_count,
            SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('returned')}) THEN 1 ELSE 0 END) as returned_count
        SQL;

        return DB::table('users')
            ->selectRaw($selectRaw)
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->when($orderCreatedAtStart, fn ($query) => $query->whereDate('orders.created_at', '>=', $orderCreatedAtStart))
            ->when($orderCreatedAtEnd, fn ($query) => $query->whereDate('orders.created_at', '<=', $orderCreatedAtEnd))
            ->groupBy('users.id');
    }
}
