<?php

namespace App\Filament\Resources\Settings\InfoPages\Pages;

use App\Filament\Resources\Settings\InfoPages\InfoPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInfoPages extends ListRecords
{
    protected static string $resource = InfoPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
