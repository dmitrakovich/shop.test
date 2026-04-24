<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\ColorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListColors extends ListRecords
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
