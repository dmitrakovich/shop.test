<?php

namespace App\Filament\Resources\Settings\Currencies\Pages;

use App\Filament\Resources\Settings\Currencies\CurrencyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
