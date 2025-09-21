<?php

namespace App\Filament\Resources\Promo\Sales\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Promo\Sales\SaleResource;
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
