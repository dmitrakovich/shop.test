<?php

namespace App\Filament\Resources\Users\Users\Pages;

use App\Filament\Resources\Users\Users\UserResource;
use App\Models\User\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->getRecord();
        $user->pruneEmptyAddresses();
    }
}
