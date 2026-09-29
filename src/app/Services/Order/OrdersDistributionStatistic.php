<?php

namespace App\Services\Order;

use App\Enums\Order\OrderTypeEnum;
use App\Models\Logs\OrderDistributionLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrdersDistributionStatistic
{
    /**
     * Manager totals for orders created in the period.
     *
     * @return Collection<int, array{
     *     __key: string,
     *     instance_name: string,
     *     distribution_count: int,
     *     distribution_percentage: float,
     *     created_manually: int,
     *     accepted_manually: int,
     *     total_count: int
     * }>
     */
    public function rows(Carbon $start, Carbon $end): Collection
    {
        $totalDistribution = OrderDistributionLog::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $rows = DB::table('orders')
            ->selectRaw(
                <<<'SQL'
                    admin_users.id as admin_id,
                    CONCAT(admin_users.user_last_name, ' ', SUBSTRING(admin_users.name, 1, 1), '.') as instance_name,
                    COUNT(DISTINCT log_order_distribution.id) as distribution_count,
                    ROUND((COUNT(DISTINCT log_order_distribution.id) / NULLIF(?, 0) * 100), 2) as distribution_percentage,
                    COUNT(DISTINCT CASE WHEN orders.order_type = ? THEN orders.id ELSE null END) as created_manually,
                    (COUNT(DISTINCT orders.id) - COUNT(DISTINCT log_order_distribution.id)) as accepted_manually,
                    COUNT(DISTINCT orders.id) as total_count
                SQL,
                [$totalDistribution, OrderTypeEnum::MANAGER->value],
            )
            ->leftJoin('log_order_distribution', 'orders.id', '=', 'log_order_distribution.order_id')
            ->leftJoin('admin_users', 'orders.admin_id', '=', 'admin_users.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('admin_users.id')
            ->orderBy('instance_name')
            ->get();

        return $rows->map(function (object $row): array {
            $instanceName = $row->instance_name;

            return [
                '__key' => (string)($row->admin_id ?? 'none'),
                'instance_name' => filled($instanceName) ? (string)$instanceName : 'Неопределено',
                'distribution_count' => (int)$row->distribution_count,
                'distribution_percentage' => round((float)($row->distribution_percentage ?? 0), 2),
                'created_manually' => (int)$row->created_manually,
                'accepted_manually' => (int)$row->accepted_manually,
                'total_count' => (int)$row->total_count,
            ];
        })->values();
    }
}
