<?php

namespace App\Filament\Resources\Seo\Cities\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('catalog_title')
                    ->label('Seo текст (в каталоге)')
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Страна')
                    ->relationship(
                        'country',
                        'name',
                        fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
            ])
            ->defaultPaginationPageOption(50);
    }
}
