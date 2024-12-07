<?php

namespace App\Filament\Resources\Registries\DefectiveProductResource\Pages;

use App\Filament\Resources\Registries\DefectiveProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDefectiveProducts extends ListRecords
{
    protected static string $resource = DefectiveProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
