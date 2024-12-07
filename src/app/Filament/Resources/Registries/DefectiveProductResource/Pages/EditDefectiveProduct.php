<?php

namespace App\Filament\Resources\Registries\DefectiveProductResource\Pages;

use App\Filament\Resources\Registries\DefectiveProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDefectiveProduct extends EditRecord
{
    protected static string $resource = DefectiveProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
