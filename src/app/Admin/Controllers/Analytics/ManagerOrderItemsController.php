<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;

class ManagerOrderItemsController extends AbstractOrderItemAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Менеджер-товар статистика';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Менеджер';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'admin_users.name';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
            ->withExpression('LastUserOrders', $this->getLastUserOrdersQuery())
            ->leftJoin('admin_users', 'orders.admin_id', '=', 'admin_users.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('admin_users.id');

        return $grid;
    }
}
