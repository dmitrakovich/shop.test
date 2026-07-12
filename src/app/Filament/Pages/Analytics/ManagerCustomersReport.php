<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Analytics\AnalyticReport;

class ManagerCustomersReport extends AnalyticReportPage
{
    protected static function report(): AnalyticReport
    {
        return AnalyticReport::ManagerCustomers;
    }
}
