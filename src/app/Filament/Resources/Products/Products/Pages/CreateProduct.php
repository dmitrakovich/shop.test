<?php

namespace App\Filament\Resources\Products\Products\Pages;

use App\Filament\Resources\Products\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
