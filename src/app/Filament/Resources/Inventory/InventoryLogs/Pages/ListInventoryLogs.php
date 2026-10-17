<?php

namespace App\Filament\Resources\Inventory\InventoryLogs\Pages;

use App\Filament\Resources\Inventory\InventoryLogs\InventoryLogResource;
use Filament\Resources\Pages\ListRecords;

class ListInventoryLogs extends ListRecords
{
    protected static string $resource = InventoryLogResource::class;

    protected ?string $subheading = 'Что автоматическое обновление наличия из 1С сделало с товарами на сайте';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
