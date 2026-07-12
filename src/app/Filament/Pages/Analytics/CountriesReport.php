<?php

namespace App\Filament\Pages\Analytics;

use App\Enums\Analytics\AnalyticReport;

class CountriesReport extends AnalyticReportPage
{
    protected static function report(): AnalyticReport
    {
        return AnalyticReport::Countries;
    }
}
