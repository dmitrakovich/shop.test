<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;

class DeliveryMethodsController extends AbstractOrderItemAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Статистика по способам доставки';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Способ доставки';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'delivery_methods.name';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
            ->leftJoin('delivery_methods', 'orders.delivery_id', '=', 'delivery_methods.id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('delivery_methods.id');

        return $grid;
    }
}
