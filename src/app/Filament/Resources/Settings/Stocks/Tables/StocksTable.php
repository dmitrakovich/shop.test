<?php

namespace App\Filament\Resources\Settings\Stocks\Tables;

use App\Enums\StockType;
use App\Filament\Actions\ToggleStockActiveAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('internal_name')
                    ->label('Внутреннее название')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Город')
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Адрес')
                    ->wrap()
                    ->toggleable(),
                IconColumn::make('check_availability')
                    ->label('Сверка')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(StockType::class),
                Filter::make('is_active')
                    ->label('Скрыть неактивные')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->defaultSort('site_sorting')
            ->reorderable('site_sorting')
            ->defaultPaginationPageOption(50)
            ->recordActions([
                ToggleStockActiveAction::make(),
                EditAction::make(),
            ]);
    }
}
