<?php

namespace App\Filament\Resources\Registries\DefectiveProducts\Pages;

use App\Filament\Resources\Registries\DefectiveProducts\DefectiveProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDefectiveProduct extends CreateRecord
{
    protected static string $resource = DefectiveProductResource::class;
}
