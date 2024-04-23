<?php

namespace App\Filament\Resources\Management\AdminUserResource\Pages;

use App\Filament\Resources\Management\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;
}
