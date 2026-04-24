<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\StyleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStyles extends ListRecords
{
    protected static string $resource = StyleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
