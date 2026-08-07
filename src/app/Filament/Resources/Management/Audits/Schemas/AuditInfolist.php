<?php

namespace App\Filament\Resources\Management\Audits\Schemas;

use App\Models\Audit;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Событие')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('event')
                                    ->label('Тип')
                                    ->badge(),
                                TextEntry::make('created_at')
                                    ->label('Когда')
                                    ->dateTime(),
                                TextEntry::make('user')
                                    ->label('Кто')
                                    ->state(fn (Audit $record): string => $record->getUserLabel() ?? 'Система / консоль'),
                                TextEntry::make('auditable')
                                    ->label('Объект')
                                    ->state(fn (Audit $record): string => $record->getAuditableLabel())
                                    ->helperText(fn (Audit $record): string => $record->auditable_type),
                                TextEntry::make('ip_address')
                                    ->label('IP')
                                    ->placeholder('—'),
                                TextEntry::make('tags')
                                    ->label('Теги')
                                    ->badge()
                                    ->separator(',')
                                    ->placeholder('—'),
                            ]),
                    ]),
                Section::make('Изменённые поля')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('changes')
                            ->hiddenLabel()
                            ->state(fn (Audit $record): array => $record->getChangesList())
                            ->table([
                                TableColumn::make('Поле'),
                                TableColumn::make('Было'),
                                TableColumn::make('Стало'),
                            ])
                            ->schema([
                                TextEntry::make('attribute'),
                                TextEntry::make('old')
                                    ->color('danger')
                                    ->placeholder('—')
                                    ->wrap(),
                                TextEntry::make('new')
                                    ->color('success')
                                    ->placeholder('—')
                                    ->wrap(),
                            ])
                            ->visible(fn (Audit $record): bool => $record->getChangesList() !== []),
                        TextEntry::make('no_changes')
                            ->hiddenLabel()
                            ->state('Нет изменённых полей')
                            ->visible(fn (Audit $record): bool => $record->getChangesList() === []),
                    ]),
                Section::make('Технические детали')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('url')
                            ->label('URL')
                            ->placeholder('—')
                            ->wrap()
                            ->columnSpanFull(),
                        TextEntry::make('user_agent')
                            ->label('User-Agent')
                            ->placeholder('—')
                            ->wrap()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
