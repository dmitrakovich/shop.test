<?php

namespace App\Filament\Resources\Registries\DefectiveProducts\Pages;

use App\Filament\Resources\Registries\DefectiveProducts\DefectiveProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDefectiveProducts extends ListRecords
{
    protected static string $resource = DefectiveProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
