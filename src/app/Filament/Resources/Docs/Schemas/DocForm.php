<?php

namespace App\Filament\Resources\Docs\Schemas;

use App\Models\Doc;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->alphaDash()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('sort')
                    ->label('Порядок')
                    ->numeric()
                    ->integer()
                    ->required()
                    ->minValue(0)
                    ->default(fn (): int => ((int)Doc::query()->max('sort')) + 10)
                    ->helperText('Меньше — выше в меню.'),
                RichEditor::make('html')
                    ->label('Текст')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
