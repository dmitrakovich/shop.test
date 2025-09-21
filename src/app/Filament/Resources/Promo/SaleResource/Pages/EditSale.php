<?php

namespace App\Filament\Resources\Promo\SaleResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Promo\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
