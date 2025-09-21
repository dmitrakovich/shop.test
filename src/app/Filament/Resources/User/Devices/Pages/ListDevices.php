<?php

namespace App\Filament\Resources\User\Devices\Pages;

use App\Filament\Resources\User\Devices\DeviceResource;
use Filament\Resources\Pages\ListRecords;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;
}
