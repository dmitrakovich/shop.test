<?php

namespace App\Filament\Resources\ProductAttributes\Pages;

use App\Filament\Resources\ProductAttributes\CountryOfOriginResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountryOfOrigin extends CreateRecord
{
    protected static string $resource = CountryOfOriginResource::class;
}
