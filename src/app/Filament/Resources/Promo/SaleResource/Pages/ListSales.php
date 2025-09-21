<?php

namespace App\Filament\Resources\Promo\SaleResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Promo\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
