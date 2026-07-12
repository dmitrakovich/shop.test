<?php

namespace App\Filament\Resources\Seo\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_id')
                    ->label('Страна')
                    ->relationship(
                        'country',
                        'name',
                        fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),
                TextInput::make('name')
                    ->label('Название города')
                    ->placeholder('Введите название города')
                    ->required()
                    ->maxLength(128),
                TextInput::make('catalog_title')
                    ->label('Seo текст (в каталоге)')
                    ->placeholder('Введите seo текст')
                    ->maxLength(128),
            ]);
    }
}
