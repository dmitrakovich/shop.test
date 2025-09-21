<?php

namespace App\Filament\Resources\Registries\DefectiveProducts\Pages;

use App\Filament\Resources\Registries\DefectiveProducts\DefectiveProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDefectiveProduct extends EditRecord
{
    protected static string $resource = DefectiveProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
