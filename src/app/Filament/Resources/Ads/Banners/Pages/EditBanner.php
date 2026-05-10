<?php

namespace App\Filament\Resources\Ads\Banners\Pages;

use App\Filament\Resources\Ads\Banners\BannerResource;
use App\Filament\Resources\Ads\Banners\Schemas\BannerForm;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return BannerForm::fillMobileTypeForDesktopOnlyPosition($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
