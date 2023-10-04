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
        $isLastUserOrder = 'orders.id IN (SELECT order_id FROM LastUserOrders)';

        return <<<SQL
        {$this->getInstanceNameColumn()} AS instance_name,
        SUM(CASE WHEN $isLastUserOrder AND orders.status_key IN ({$this->statuses['accepted']}) THEN 1 ELSE 0 END) AS accepted_count,
        SUM(CASE WHEN $isLastUserOrder AND orders.status_key IN ({$this->statuses['in_progress']}) THEN 1 ELSE 0 END) AS in_progress_count,
        SUM(CASE WHEN $isLastUserOrder AND orders.status_key IN ({$this->statuses['purchased']}) THEN 1 ELSE 0 END) AS purchased_count,
        SUM(CASE WHEN $isLastUserOrder AND orders.status_key IN ({$this->statuses['canceled']}) THEN 1 ELSE 0 END) AS canceled_count,
        SUM(CASE WHEN $isLastUserOrder AND orders.status_key IN ({$this->statuses['returned']}) THEN 1 ELSE 0 END) AS returned_count,
        SUM(CASE WHEN $isLastUserOrder THEN 1 ELSE 0 END) AS total_count,
        ROUND(SUM(CASE WHEN orders.status_key IN ({$this->statuses['purchased']}) AND order_items.status_key IN ({$this->statuses['purchased']}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_purchased_price,
        ROUND(SUM(CASE WHEN orders.status_key IN ({$this->statuses['lost']}) AND order_items.status_key IN ({$this->statuses['lost']}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_lost_price
        SQL;
    }

    /**
     * Get a query to retrieve the last order ID for each user.
     */
    protected function getLastUserOrdersQuery(): Builder
    {
        return DB::table('users')
            ->select(['users.id as user_id', DB::raw('MAX(orders.id) AS order_id')])
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->whereNotNull('orders.id')
            ->groupBy('users.id');
    }
}
