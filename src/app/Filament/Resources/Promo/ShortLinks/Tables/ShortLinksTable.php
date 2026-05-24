<?php

namespace App\Filament\Resources\Promo\ShortLinks\Tables;

use App\Models\ShortLink;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShortLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('short_link')
                    ->label('Короткая ссылка')
                    ->formatStateUsing(fn (ShortLink $record): string => route('short-link', $record, true))
                    ->copyable()
                    ->searchable(),
                TextColumn::make('full_link')
                    ->label('Полная ссылка')
                    ->limit(120)
                    ->wrap()
                    ->copyable()
                    ->searchable(),
                TextColumn::make('hits_count')
                    ->label('Переходов')
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label('Последний переход')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('id', 'desc');
    }
}
