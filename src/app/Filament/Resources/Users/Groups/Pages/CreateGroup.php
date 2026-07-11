<?php

namespace App\Filament\Resources\Users\Groups\Pages;

use App\Filament\Resources\Users\Groups\GroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;
}
