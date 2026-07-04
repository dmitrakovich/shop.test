<?php

namespace App\Filament\Resources\Seo\SeoPages\Pages;

use App\Filament\Resources\Seo\SeoPages\SeoPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeoPages extends ListRecords
{
    protected static string $resource = SeoPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
