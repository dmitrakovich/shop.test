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

        // // $qq = User::selectRaw($this->getSelectSql())
        // //     ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
        // //     // ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
        // //     ->groupBy('instance_name', 'users.id');

        // //     print_r($qq->toSql());
        // // exit();

        // // users.id AS user_id,
        // // orders.created_at AS created_at,

        // $orderCreatedAtStart = request()->input('order_created_at_start');
        // $orderCreatedAtEnd = request()->input('order_created_at_end');


        // $subQuery = <<<SQL
        // (SELECT {$this->getInstanceNameColumn()} AS instance_name,
        // users.id AS user_id,
        // SUM(CASE WHEN orders.status_key IN ({$this->statuses['accepted']}) THEN DISTINCT() ELSE 0 END) AS accepted_count,
        // SUM(CASE WHEN orders.status_key IN ({$this->statuses['in_progress']}) THEN 1 ELSE 0 END) AS in_progress_count,
        // SUM(CASE WHEN orders.status_key IN ({$this->statuses['purchased']}) THEN 1 ELSE 0 END) AS purchased_count,
        // SUM(CASE WHEN orders.status_key IN ({$this->statuses['canceled']}) THEN 1 ELSE 0 END) AS canceled_count,
        // SUM(CASE WHEN orders.status_key IN ({$this->statuses['returned']}) THEN 1 ELSE 0 END) AS returned_count,
        // SUM(1) AS total_count
        // FROM orders LEFT JOIN users ON users.id = orders.user_id
        // GROUP BY instance_name) AS userOrders
        // SQL;

        // // dd(\DB::statement($subQuery));

        // $grid->model()
        //     ->select('instance_name', 'accepted_count', 'in_progress_count', 'purchased_count', 'canceled_count', 'returned_count', 'total_count')
        //     ->leftJoin(
        //         \DB::raw($subQuery),
        //         function ($join) {
        //             $join->on('users.id', '=', 'userOrders.user_id');
        //         }
        //     );

        $sub = \DB::table('orders')
            ->selectRaw($this->getSelectSql())
            ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', '2024-01-04 00:00:00')
            ->groupBy('instance_name');


        $grid->model()
            ->select('qq.instance_name', 'qq.total_purchased_price', 'qq.total_lost_price')
            ->from($sub, 'qq');
            // ->leftJoin('users', 'users.id', '=', 'orders.user_id')
            // ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
            // ->groupBy('instance_name');

        return $grid;
    }
}
