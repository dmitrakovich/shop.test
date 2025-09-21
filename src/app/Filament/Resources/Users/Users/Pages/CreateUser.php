<?php

namespace App\Filament\Resources\Users\Users\Pages;

use App\Filament\Resources\Users\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
