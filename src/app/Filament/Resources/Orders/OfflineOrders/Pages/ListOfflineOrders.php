<?php

namespace App\Filament\Resources\Orders\OfflineOrders\Pages;

use App\Filament\Resources\Orders\OfflineOrders\OfflineOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListOfflineOrders extends ListRecords
{
    protected static string $resource = OfflineOrderResource::class;

    protected ?string $subheading = 'Продажи и возвраты в магазинах из 1С';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
