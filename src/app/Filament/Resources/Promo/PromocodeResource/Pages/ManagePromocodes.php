<?php

namespace App\Filament\Resources\Promo\PromocodeResource\Pages;

use App\Filament\Resources\Promo\PromocodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePromocodes extends ManageRecords
{
    protected static string $resource = PromocodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
