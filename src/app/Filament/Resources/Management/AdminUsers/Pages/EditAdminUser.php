<?php

namespace App\Filament\Resources\Management\AdminUsers\Pages;

use App\Filament\Resources\Management\AdminUsers\AdminUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
