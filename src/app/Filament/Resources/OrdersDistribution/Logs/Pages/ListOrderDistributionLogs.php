<?php

namespace App\Filament\Resources\OrdersDistribution\Logs\Pages;

use App\Filament\Resources\OrdersDistribution\Logs\OrderDistributionLogResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderDistributionLogs extends ListRecords
{
    protected static string $resource = OrderDistributionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
