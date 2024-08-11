<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;

class OrderTypeController extends AbstractCustomerAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Статистика по типу заказов';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Тип заказа';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'order_type';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order);

        $grid->model()->selectRaw($this->getSelectSql())
            ->withExpression('UserOrderStatusCount', $this->getUserOrderStatusCountQuery())
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('order_type');

        return $grid;
    }
}
