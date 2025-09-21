<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use App\Enums\User\BanReason;
use App\Models\User\Device;
use App\Models\User\User;

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

        return $record->isSomeDevicesBanned();
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
            $record->devices->each(
                fn (Device $device) => self::isBanned($record) ? $device->unban() : $device->ban(BanReason::BY_ADMIN)
            );
        }
    }
}
