<?php

namespace App\Filament\Resources\Product\Sizes\Pages;

use App\Filament\Resources\Product\Sizes\SizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListSizes extends ListRecords
{
    protected static string $resource = SizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::FiveExtraLarge;
    }
}
