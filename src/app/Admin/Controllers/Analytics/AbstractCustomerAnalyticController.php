<?php

namespace App\Admin\Controllers\Analytics;

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
        SUM(CASE WHEN orders.status_key IN ({$this->statuses['purchased']}) AND order_items.status_key IN ({$this->statuses['purchased']}) THEN order_items.current_price ELSE 0 END) AS total_purchased_price,
        SUM(CASE WHEN orders.status_key IN ({$this->statuses['lost']}) AND order_items.status_key IN ({$this->statuses['lost']}) THEN order_items.current_price ELSE 0 END) AS total_lost_price,
        (SUM(CASE WHEN order_items.status_key IN ({$this->statuses['purchased']}) THEN 1 ELSE 0 END) / COUNT(order_items.id)) * 100 AS purchase_percentage
        SQL;
    }

    /**
     * Get the name of the database table associated with the analysis instance (Abstract Method).
     */
    abstract protected function getInstanceNameColumn(): string;
}
