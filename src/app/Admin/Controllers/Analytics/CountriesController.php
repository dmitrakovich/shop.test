<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Country;
use Encore\Admin\Grid;

class CountriesController extends AbstractAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Статистика по странам';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Страна';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Country());

        $isLastUserOrder = 'orders.id IN (SELECT order_id FROM LastUserOrders)';
        $select = <<<SQL
        countries.name AS instance_name,
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

        $grid->model()->selectRaw($select)
            ->withExpression('LastUserOrders', $this->getLastUserOrdersQuery())
            ->leftJoin('user_addresses', 'countries.id', '=', 'user_addresses.country_id')
            ->leftJoin('users', 'user_addresses.user_id', '=', 'users.id')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('countries.id');

        return $grid;
    }
}
