<?php

namespace App\Filament\Resources\Promo\Promocodes\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Promo\Promocodes\PromocodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePromocodes extends ManageRecords
{
    protected static string $resource = PromocodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
