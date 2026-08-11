<?php

namespace App\Filament\Resources\Settings\Stocks\Pages;

use App\Filament\Actions\ToggleStockActiveAction;
use App\Filament\Resources\Settings\Stocks\StockResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditStock extends EditRecord
{
    protected static string $resource = StockResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            ToggleStockActiveAction::make(),
        ];
    }
}
