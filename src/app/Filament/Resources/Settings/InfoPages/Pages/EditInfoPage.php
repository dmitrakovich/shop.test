<?php

namespace App\Filament\Resources\Settings\InfoPages\Pages;

use App\Filament\Resources\Settings\InfoPages\InfoPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfoPage extends EditRecord
{
    protected static string $resource = InfoPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
