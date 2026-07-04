<?php

namespace App\Filament\Resources\Seo\SeoPages\Tables;

use App\Enums\Seo\SeoPageType;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeoPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('page_type')
                    ->label('Тип')
                    ->badge()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                TextColumn::make('h1')
                    ->label('H1')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('tag_name')
                    ->label('Тег')
                    ->wrap()
                    ->limit(40),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('page_type')
                    ->label('Тип страницы')
                    ->options(SeoPageType::class)
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
            ])
            ->defaultPaginationPageOption(50);
    }
}
