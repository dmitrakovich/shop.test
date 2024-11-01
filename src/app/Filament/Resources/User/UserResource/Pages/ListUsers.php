<?php

namespace App\Filament\Resources\User\UserResource\Pages;

use App\Exports\UsersToSmsTrafficExport;
use App\Filament\Resources\User\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export-users-to-sms-traffic')
                ->label('Выгрузить пользователей для sms traffic')
                ->icon('heroicon-m-arrow-up-tray')
                ->action(fn () => new UsersToSmsTrafficExport($this->getFilteredTableQuery())),
            Actions\CreateAction::make(),
        ];
    }
}
