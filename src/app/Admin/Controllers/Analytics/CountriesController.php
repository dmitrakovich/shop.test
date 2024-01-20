<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;

class CountriesController extends AbstractCustomerAnalyticController
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
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'countries.name';
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
            ->leftJoin('user_addresses', 'user_addresses.user_id', '=', 'users.id')
            ->leftJoin('countries', 'countries.id', '=', 'user_addresses.country_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('countries.id');

        return $grid;
    }
}
