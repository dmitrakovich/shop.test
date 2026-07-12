<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Analytics\AnalyticReport;

class DeliveryMethodsReport extends AnalyticReportPage
{
    protected static function report(): AnalyticReport
    {
        return AnalyticReport::DeliveryMethods;
    }
}
