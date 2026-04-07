<?php

namespace App\Filament\Resources\Ads\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Позиция')
                    ->badge(),
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Баннер')
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('url')
                    ->label('Ссылка')
                    ->searchable(),
                // TextColumn::make('priority')
                //     ->label('Приоритет')
                //     ->numeric()
                //     ->sortable(),
                ToggleColumn::make('active')
                    ->label('Активен'),
                TextColumn::make('start_datetime')
                    ->label('Дата начала')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_datetime')
                    ->label('Дата окончания')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлен')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Удален')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // IconColumn::make('show_timer')
                //     ->label('Показывать таймер')
                //     ->boolean(),
            ])
            ->filters([
                TrashedFilter::make()->native(false),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
                DeleteAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
