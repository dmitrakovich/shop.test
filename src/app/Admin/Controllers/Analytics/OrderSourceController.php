<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use App\Models\User\User;
use Encore\Admin\Grid;

class OrderSourceController extends AbstractCustomerAnalyticController
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
        return 'CONCAT(orders.utm_source, \'-\', orders.utm_campaign)';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
        ->withExpression('UserOrderStatusCount', $this->getUserOrderStatusCountQuery())
        ->leftJoin('users', 'users.id', '=', 'orders.user_id')
        ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
        ->groupBy('instance_name');

        return $grid;
    }
}
