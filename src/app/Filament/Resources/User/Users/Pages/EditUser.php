<?php

namespace App\Filament\Resources\User\Users\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\User\Users\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
