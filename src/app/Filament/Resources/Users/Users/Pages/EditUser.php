<?php

namespace App\Filament\Resources\Users\Users\Pages;

use App\Filament\Resources\Users\Users\UserResource;
use App\Models\User\User;
use Filament\Actions\DeleteAction;
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

    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $user->pruneEmptyAddresses();
    }
}
