<?php

namespace App\Filament\Resources\Docs\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

class DocsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort')
                    ->label('Порядок')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('html')
                    ->label('Текст')
                    ->formatStateUsing(fn (?string $state): string => Str::limit(strip_tags((string)$state), 80))
                    ->wrap(),
                TextColumn::make('updated_at')
                    ->label('Дата редактирования')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->after(fn (Component $livewire) => $livewire->dispatch('refresh-sidebar')),
            ]);
    }
}
