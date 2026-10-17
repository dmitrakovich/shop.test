<?php

namespace App\Filament\Resources\Orders\OrderItemStatusLogs\Pages;

use App\Filament\Resources\Orders\OrderItemStatusLogs\OrderItemStatusLogResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderItemStatusLogs extends ListRecords
{
    protected static string $resource = OrderItemStatusLogResource::class;

    protected ?string $subheading = 'Путь каждой единицы из заказа по складам и магазинам: от подтверждения до выкупа или возврата';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
