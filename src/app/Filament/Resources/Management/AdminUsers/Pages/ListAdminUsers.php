<?php

namespace App\Filament\Resources\Management\AdminUsers\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Management\AdminUsers\AdminUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdminUsers extends ListRecords
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
