<?php

namespace App\Filament\Resources\Users\Users\Pages;

use App\Exports\UsersToSmsTrafficExport;
use App\Filament\Resources\Users\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export-users-to-sms-traffic')
                ->label('Выгрузить пользователей для sms traffic')
                ->icon('heroicon-m-arrow-up-tray')
                ->action(fn () => new UsersToSmsTrafficExport($this->getFilteredTableQuery())),
            CreateAction::make(),
        ];
    }
}
