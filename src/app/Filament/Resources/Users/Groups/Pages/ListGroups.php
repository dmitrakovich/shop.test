<?php

namespace App\Filament\Resources\Users\Groups\Pages;

use App\Filament\Resources\Users\Groups\GroupResource;
use Filament\Resources\Pages\ListRecords;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TODO: enable when product TZ for user groups is ready.
            // CreateAction::make(),
        ];
    }
}
