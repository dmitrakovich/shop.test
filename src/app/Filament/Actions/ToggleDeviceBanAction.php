<?php

namespace App\Filament\Actions;

use App\Enums\User\BanReason;
use App\Models\User\Device;
use App\Models\User\User;
use Filament\Tables\Actions\Action;

class ToggleDeviceBanAction
{
    public static function make(): Action
    {
        return Action::make('toggleBan')
            ->label(fn (Device|User $record) => self::getLabel($record))
            ->icon(fn (Device|User $record) => self::getIcon($record))
            ->color(fn (Device|User $record) => self::getColor($record))
            ->requiresConfirmation()
            ->action(fn (Device|User $record) => self::toggleRecordBan($record));
    }

    private static function isBanned(Device|User $record): bool
    {
        if ($record instanceof Device) {
            return $record->isBanned();
        }

        if ($record instanceof User) {
            return $record->isSomeDevicesBanned();
        }

        return false;
    }

    private static function getLabel(Device|User $record): string
    {
        return self::isBanned($record) ? 'Разбанить' : 'Забанить';
    }

    private static function getIcon(Device|User $record): string
    {
        return self::isBanned($record) ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed';
    }

    private static function getColor(Device|User $record): string
    {
        return self::isBanned($record) ? 'success' : 'danger';
    }

    private static function toggleRecordBan(Device|User $record): void
    {
        if ($record instanceof Device) {
            $record->toggleBan(BanReason::BY_ADMIN);
        }

        if ($record instanceof User) {
            $action = self::isBanned($record) ? 'unban' : 'ban';
            $record->devices->each(fn (Device $device) => $device->$action(BanReason::BY_ADMIN));
        }
    }
}
