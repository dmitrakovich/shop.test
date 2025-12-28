<?php

namespace App\Admin\Controllers\Analytics;

abstract class AbstractOrderItemAnalyticController extends AbstractAnalyticController
{
    /**
     * Get the SQL SELECT statement for the analysis.
     */
    protected function getSelectSql(): string
    {
        return <<<SQL
        {$this->getInstanceNameColumn()} AS instance_name,
        SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('accepted')}) THEN 1 ELSE 0 END) AS accepted_count,
        SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('in_progress')}) THEN 1 ELSE 0 END) AS in_progress_count,
        SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('purchased')}) THEN 1 ELSE 0 END) AS purchased_count,
        SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('canceled')}) THEN 1 ELSE 0 END) AS canceled_count,
        SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('returned')}) THEN 1 ELSE 0 END) AS returned_count,
        COUNT(order_items.id) AS total_count,
        ROUND(SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('purchased')}) AND order_items.status IN ({$this->getItemStatusesForQuery('purchased')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_purchased_price,
        ROUND(SUM(CASE WHEN orders.status IN ({$this->getStatusesForQuery('lost')}) AND order_items.status IN ({$this->getItemStatusesForQuery('lost')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_lost_price
        SQL;
    }
}
