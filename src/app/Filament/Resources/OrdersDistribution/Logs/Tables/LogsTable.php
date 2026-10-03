<?php

namespace App\Filament\Resources\OrdersDistribution\Logs\Tables;

use App\Models\Logs\OrderDistributionLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата и время')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('order_id')
                    ->label('№ заказа'),
                TextColumn::make('action')
                    ->label('Комментарий')
                    ->wrap(),
                TextColumn::make('manager')
                    ->label('Менеджер')
                    ->state(fn (OrderDistributionLog $record): ?string => $record->admin?->short_name)
                    ->placeholder('—'),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->selectable(false)
            ->columnManager(false);
    }
}
