<?php

namespace App\Admin\Controllers\Analytics;

use App\Models\Orders\Order;
use Encore\Admin\Grid;
use Illuminate\Database\Query\JoinClause;

class InstallmentController extends AbstractOrderItemAnalyticController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Статистика по рассрочке';

    /**
     * Get the column title for the countries
     */
    protected function getInstanceColumnTitle(): string
    {
        return 'Рассрочка';
    }

    /**
     * {@inheritdoc}
     */
    protected function getInstanceNameColumn(): string
    {
        return 'payment_methods.name';
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
            ->leftJoin('payment_methods', function (JoinClause $join) {
                $join->on('payment_methods.id', '=', 'orders.payment_id')
                     ->where('payment_methods.id', '=', 4);
            })
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('payment_methods.id');

        return $grid;
    }
}
