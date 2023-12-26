<?php

namespace App\Admin\Controllers\Analytics;

use App\Enums\Order\UtmEnum;

use App\Models\Orders\Order;
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
        return 'CONCAT(utm_source, \'-\', utm_campaign)';
    }

    /**
     * Generates additional grid columns for the given grid.
     *
     * @param $grid The grid object to generate columns for.
     * @return void
     */
    protected function additionalGridColumns($grid): void
    {
        $grid->column('channel_name', 'Канал')->display(fn () => UtmEnum::tryFrom($this->instance_name)?->channelName());
        $grid->column('company_name', 'Компания')->display(fn () => UtmEnum::tryFrom($this->instance_name)?->companyName());
    }

    /**
     * Get a prepared grid for analyzing order statistics by country.
     */
    protected function getPreparedGrid(): Grid
    {
        $grid = new Grid(new Order());

        $grid->model()->selectRaw($this->getSelectSql())
            ->withExpression('LastUserOrders', $this->getLastUserOrdersQuery())
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->groupBy('instance_name');

        return $grid;
    }
}
