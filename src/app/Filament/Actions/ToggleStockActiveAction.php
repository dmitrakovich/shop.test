<?php

namespace App\Filament\Actions;

use App\Jobs\AvailableSizes\UpdateAvailabilityJob;
use App\Models\Stock;
use App\Services\LogService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class ToggleStockActiveAction
{
    public static function make(): Action
    {
        return Action::make('toggleActive')
            ->label(fn (Stock $record): string => $record->is_active ? 'Отключить' : 'Включить')
            ->icon(fn (Stock $record): Heroicon => $record->is_active ? Heroicon::OutlinedEyeSlash : Heroicon::OutlinedEye)
            ->color(fn (Stock $record): string => $record->is_active ? 'danger' : 'success')
            ->requiresConfirmation()
            ->modalHeading(fn (Stock $record): string => $record->is_active
                ? 'Отключить склад / магазин?'
                : 'Включить склад / магазин?')
            ->modalDescription(fn (Stock $record): string => $record->is_active
                ? 'Склад скроется с сайта и из ПВЗ. Наличие будет синхронизировано с 1С и каталог пересчитан сразу после подтверждения.'
                : 'Склад снова появится на сайте. Наличие будет синхронизировано с 1С и каталог пересчитан сразу после подтверждения.')
            ->modalSubmitActionLabel(fn (Stock $record): string => $record->is_active ? 'Отключить и пересчитать' : 'Включить и пересчитать')
            ->action(function (Stock $record, LogService $logService): void {
                $activating = !$record->is_active;

                $record->update(['is_active' => $activating]);
                UpdateAvailabilityJob::dispatchSync($logService);

                Notification::make()
                    ->title($activating ? 'Склад включён, каталог пересчитан' : 'Склад отключён, каталог пересчитан')
                    ->success()
                    ->send();
            });
    }
}
