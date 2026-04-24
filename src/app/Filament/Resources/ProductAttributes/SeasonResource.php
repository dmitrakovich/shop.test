<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateSeason;
use App\Filament\Resources\ProductAttributes\Pages\EditSeason;
use App\Filament\Resources\ProductAttributes\Pages\ListSeasons;
use App\Models\Season;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::ProductAttributes;

    protected static ?string $modelLabel = 'Сезон';

    protected static ?string $pluralModelLabel = 'Сезоны';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255),
                Textarea::make('seo')
                    ->label('SEO')
                    ->rows(4),
                Toggle::make('is_actual')
                    ->label('Актуальный сезон')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название')->searchable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('seo')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_actual')
                    ->label('Актуальный')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }
}
