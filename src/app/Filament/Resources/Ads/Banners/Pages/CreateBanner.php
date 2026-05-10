<?php

namespace App\Filament\Resources\Ads\Banners\Pages;

use App\Filament\Resources\Ads\Banners\BannerResource;
use App\Filament\Resources\Ads\Banners\Schemas\BannerForm;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return BannerForm::fillMobileTypeForDesktopOnlyPosition($data);
    }
}
