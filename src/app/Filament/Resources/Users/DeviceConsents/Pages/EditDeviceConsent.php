<?php

namespace App\Filament\Resources\Users\DeviceConsents\Pages;

use App\Filament\Resources\Users\DeviceConsents\DeviceConsentResource;
use Filament\Resources\Pages\EditRecord;

class EditDeviceConsent extends EditRecord
{
    protected static string $resource = DeviceConsentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
