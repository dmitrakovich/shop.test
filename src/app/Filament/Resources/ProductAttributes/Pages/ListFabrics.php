<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\FabricResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFabrics extends ListRecords
{
    protected static string $resource = FabricResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
