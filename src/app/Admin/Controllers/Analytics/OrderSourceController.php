<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;

class OrderSourceController extends AbstractOrderItemAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Статистика по источникам заказов';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Источник заказа';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'CONCAT(utm_source, \'-\', utm_campaign)';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('instance_name');

        return $grid;
    }
}
