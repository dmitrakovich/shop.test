<?php

namespace Tests\Unit\Enums;

use App\Enums\Order\OrderItemStatusLogStage;
use App\Models\Logs\OrderItemStatusLog;
use Tests\TestCase;

class OrderItemStatusLogStageTest extends TestCase
{
    public function test_new_unit_without_stage_dates_is_new(): void
    {
        $this->assertSame(OrderItemStatusLogStage::New, $this->makeLog([])->getStage());
    }

    public function test_latest_passed_stage_is_current(): void
    {
        $log = $this->makeLog([
            'confirmed_at' => '2026-07-01 10:00:00',
            'collected_at' => '2026-07-01 11:00:00',
            'sended_at' => '2026-07-02 09:00:00',
        ]);

        $this->assertSame(OrderItemStatusLogStage::Sent, $log->getStage());
    }

    public function test_return_wins_over_completion(): void
    {
        $log = $this->makeLog([
            'completed_at' => '2026-07-03 10:00:00',
            'returned_at' => '2026-07-10 10:00:00',
        ]);

        $this->assertSame(OrderItemStatusLogStage::Returned, $log->getStage());
    }

    public function test_out_of_stock_wins_over_any_other_stage(): void
    {
        $log = $this->makeLog([
            'confirmed_at' => '2026-07-01 10:00:00',
            'deleted_at' => '2026-07-01 12:00:00',
        ]);

        $this->assertSame(OrderItemStatusLogStage::OutOfStock, $log->getStage());
    }

    public function test_query_requires_own_date_and_no_later_stage_dates(): void
    {
        $query = OrderItemStatusLog::query()->withTrashed();

        OrderItemStatusLogStage::Completed->applyToQuery($query);

        $this->assertSame(
            'select * from `log_order_item_statuses` where `deleted_at` is null and `canceled_at` is null'
                . ' and `returned_at` is null and `completed_at` is not null',
            $query->toSql(),
        );
    }

    /**
     * @param  array<string, string>  $dates
     */
    private function makeLog(array $dates): OrderItemStatusLog
    {
        return new OrderItemStatusLog()->setRawAttributes([
            'order_item_id' => 1,
            'stock_id' => 1,
            'created_at' => '2026-07-01 09:00:00',
            ...$dates,
        ]);
    }
}
