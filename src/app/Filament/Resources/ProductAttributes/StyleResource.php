<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateStyle;
use App\Filament\Resources\ProductAttributes\Pages\EditStyle;
use App\Filament\Resources\ProductAttributes\Pages\ListStyles;
use App\Models\Style;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StyleResource extends Resource
{
    protected static ?string $model = Style::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Стиль';

    protected static ?string $pluralModelLabel = 'Стили';

    protected static ?int $navigationSort = 9;

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->label('Название')->searchable()->sortable(),
                TextColumn::make('slug')->searchable()->sortable(),
                TextColumn::make('seo')->limit(40)->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListStyles::route('/'),
            'create' => CreateStyle::route('/create'),
            'edit' => EditStyle::route('/{record}/edit'),
        ];
    }
}
