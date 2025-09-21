<?php

namespace App\Filament\Resources\Management\AdminUsers\Pages;

use App\Filament\Resources\Management\AdminUsers\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;
}
