<?php

namespace Tests\Unit\Services\Analytics;

use App\Enums\Analytics\AnalyticReport;
use App\Services\Analytics\AnalyticReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticReportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_reports_return_empty_collection_without_orders(): void
    {
        $service = app(AnalyticReportService::class);

        foreach (AnalyticReport::cases() as $report) {
            $rows = $service->rows($report);

            $this->assertTrue($rows->isEmpty(), $report->value . ' should be empty');
            $this->assertSame(0, $service->totals($rows)['total_count']);
        }
    }
}
