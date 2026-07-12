<?php

namespace App\Services\Analytics;

use App\Enums\Analytics\AnalyticAggregation;
use App\Enums\Analytics\AnalyticReport;
use App\Enums\Order\UtmEnum;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelCte\Query\Builder as QueryBuilder;

final class AnalyticReportService
{
    /**
     * @return Collection<int|string, array<string, mixed>>
     */
    public function rows(
        AnalyticReport $report,
        ?CarbonInterface $start = null,
        ?CarbonInterface $end = null,
    ): Collection {
        /** @var QueryBuilder $query */
        $query = DB::table('orders')->selectRaw($this->selectSql($report));

        if ($report->aggregation() === AnalyticAggregation::Customer) {
            $query->withExpression(
                'UserOrderStatusCount',
                $this->userOrderStatusCountQuery($start, $end),
            );
        }

        $report->configureQuery($query);
        $this->applyDateRange($query, $start, $end);

        /** @var Collection<int, object> $rows */
        $rows = $query
            ->orderByDesc('total_purchased_price')
            ->get();

        /** @var Collection<int|string, array<string, mixed>> $mapped */
        $mapped = $rows
            ->values()
            ->mapWithKeys(function (object $row, int $index) use ($report): array {
                $rawInstanceName = $row->instance_name !== null && $row->instance_name !== ''
                    ? (string)$row->instance_name
                    : null;
                $totalCount = (int)$row->total_count;
                $purchasedCount = (int)$row->purchased_count;

                $record = [
                    'instance_name' => $this->formatInstanceName($rawInstanceName),
                    'total_count' => $totalCount,
                    'accepted_count' => (int)$row->accepted_count,
                    'in_progress_count' => (int)$row->in_progress_count,
                    'purchased_count' => $purchasedCount,
                    'canceled_count' => (int)$row->canceled_count,
                    'returned_count' => (int)$row->returned_count,
                    'total_purchased_price' => round((float)$row->total_purchased_price, 2),
                    'purchase_percentage' => $totalCount > 0
                        ? round(($purchasedCount / $totalCount) * 100, 2)
                        : 0.0,
                    'total_lost_price' => round((float)$row->total_lost_price, 2),
                ];

                if ($report->showsUtmDetails()) {
                    $utm = $rawInstanceName !== null ? UtmEnum::tryFrom($rawInstanceName) : null;
                    $record['channel_name'] = $utm?->channelName();
                    $record['company_name'] = $utm?->companyName();
                }

                return [$index + 1 => $record];
            });

        return $mapped;
    }

    /**
     * @param  Collection<int|string, array<string, mixed>>  $rows
     * @return array<string, float|int>
     */
    public function totals(Collection $rows): array
    {
        $totalCount = (int)$rows->sum('total_count');

        return [
            'total_count' => $totalCount,
            'accepted_count' => (int)$rows->sum('accepted_count'),
            'in_progress_count' => (int)$rows->sum('in_progress_count'),
            'purchased_count' => (int)$rows->sum('purchased_count'),
            'canceled_count' => (int)$rows->sum('canceled_count'),
            'returned_count' => (int)$rows->sum('returned_count'),
            'total_purchased_price' => round((float)$rows->sum('total_purchased_price'), 2),
            'purchase_percentage' => round(
                (float)$rows->avg(
                    static fn (array $row): float => (float)$row['purchase_percentage'],
                ),
                2,
            ),
            'total_lost_price' => round((float)$rows->sum('total_lost_price'), 2),
        ];
    }

    private function selectSql(AnalyticReport $report): string
    {
        $instanceName = $report->instanceNameExpression();

        if ($report->aggregation() === AnalyticAggregation::Customer) {
            return <<<SQL
                {$instanceName} AS instance_name,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where purchased_count >= 1)) THEN orders.user_id ELSE null END) AS purchased_count,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where progress_count >= 1 and purchased_count = 0)) THEN orders.user_id ELSE null END) AS in_progress_count,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where returned_count >= 1 and purchased_count = 0 and progress_count = 0)) THEN orders.user_id ELSE null END) AS returned_count,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where canceled_count >= 1 and purchased_count = 0 and progress_count = 0 and returned_count = 0)) THEN orders.user_id ELSE null END) AS canceled_count,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount where accepted_count >= 1 and purchased_count = 0 and progress_count = 0 and returned_count = 0 and canceled_count = 0)) THEN orders.user_id ELSE null END) AS accepted_count,
                COUNT(DISTINCT CASE WHEN (orders.user_id IN (SELECT user_id FROM UserOrderStatusCount)) THEN orders.user_id ELSE null END) AS total_count,
                ROUND(SUM(CASE WHEN orders.status IN ({$this->orderStatuses('purchased')}) AND order_items.status IN ({$this->itemStatuses('purchased')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_purchased_price,
                ROUND(SUM(CASE WHEN orders.status IN ({$this->orderStatuses('lost')}) AND order_items.status IN ({$this->itemStatuses('lost')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_lost_price
            SQL;
        }

        return <<<SQL
            {$instanceName} AS instance_name,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('accepted')}) THEN 1 ELSE 0 END) AS accepted_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('in_progress')}) THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('purchased')}) THEN 1 ELSE 0 END) AS purchased_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('canceled')}) THEN 1 ELSE 0 END) AS canceled_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('returned')}) THEN 1 ELSE 0 END) AS returned_count,
            COUNT(order_items.id) AS total_count,
            ROUND(SUM(CASE WHEN orders.status IN ({$this->orderStatuses('purchased')}) AND order_items.status IN ({$this->itemStatuses('purchased')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_purchased_price,
            ROUND(SUM(CASE WHEN orders.status IN ({$this->orderStatuses('lost')}) AND order_items.status IN ({$this->itemStatuses('lost')}) THEN order_items.current_price / orders.rate ELSE 0 END), 2) AS total_lost_price
        SQL;
    }

    private function userOrderStatusCountQuery(
        ?CarbonInterface $start,
        ?CarbonInterface $end,
    ): QueryBuilder {
        $selectRaw = <<<SQL
            users.id as user_id,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('purchased')}) THEN 1 ELSE 0 END) as purchased_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('accepted')}) THEN 1 ELSE 0 END) as accepted_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('in_progress')}) THEN 1 ELSE 0 END) as progress_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('canceled')}) THEN 1 ELSE 0 END) as canceled_count,
            SUM(CASE WHEN orders.status IN ({$this->orderStatuses('returned')}) THEN 1 ELSE 0 END) as returned_count
        SQL;

        /** @var QueryBuilder $query */
        $query = DB::table('users')
            ->selectRaw($selectRaw)
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->when($start !== null, function ($builder) use ($start): void {
                $builder->where('orders.created_at', '>=', $start);
            })
            ->when($end !== null, function ($builder) use ($end): void {
                $builder->where('orders.created_at', '<=', $end);
            })
            ->groupBy('users.id');

        return $query;
    }

    private function applyDateRange(
        QueryBuilder $query,
        ?CarbonInterface $start,
        ?CarbonInterface $end,
    ): void {
        if ($start !== null) {
            $query->where('orders.created_at', '>=', $start);
        }

        if ($end !== null) {
            $query->where('orders.created_at', '<=', $end);
        }
    }

    private function formatInstanceName(?string $rawInstanceName): string
    {
        return $rawInstanceName ?? 'Неопределено';
    }

    private function orderStatuses(string $bucket): string
    {
        return AnalyticStatusGroups::orderStatusIds($bucket);
    }

    private function itemStatuses(string $bucket): string
    {
        return AnalyticStatusGroups::itemStatusIds($bucket);
    }
}
