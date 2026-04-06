<?php

namespace App\Filament\Resources\Users\DeviceConsents\Pages;

use App\Filament\Resources\Users\DeviceConsents\DeviceConsentResource;
use Filament\Resources\Pages\ListRecords;

class ListDeviceConsents extends ListRecords
{
    protected static string $resource = DeviceConsentResource::class;
}
