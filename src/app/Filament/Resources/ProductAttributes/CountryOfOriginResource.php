<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateCountryOfOrigin;
use App\Filament\Resources\ProductAttributes\Pages\EditCountryOfOrigin;
use App\Filament\Resources\ProductAttributes\Pages\ListCountriesOfOrigin;
use App\Models\ProductAttributes\CountryOfOrigin;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountryOfOriginResource extends Resource
{
    protected static ?string $model = CountryOfOrigin::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Страна производитель';

    protected static ?string $pluralModelLabel = 'Страны производства';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255),
                Textarea::make('seo')
                    ->label('SEO')
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название')->searchable(),
                TextColumn::make('address')->limit(40),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('seo')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCountriesOfOrigin::route('/'),
            'create' => CreateCountryOfOrigin::route('/create'),
            'edit' => EditCountryOfOrigin::route('/{record}/edit'),
        ];
    }
}
