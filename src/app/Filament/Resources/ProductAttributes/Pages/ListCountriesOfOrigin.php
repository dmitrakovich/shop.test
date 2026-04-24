<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\CountryOfOriginResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCountriesOfOrigin extends ListRecords
{
    protected static string $resource = CountryOfOriginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
