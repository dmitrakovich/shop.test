<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateColor;
use App\Filament\Resources\ProductAttributes\Pages\EditColor;
use App\Filament\Resources\ProductAttributes\Pages\ListColors;
use App\Models\Color;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Цвет';

    protected static ?string $pluralModelLabel = 'Цвета';

    protected static ?int $navigationSort = 6;

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
                TextInput::make('value')
                    ->label('Код цвета')
                    ->default('#ffffff')
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
                TextColumn::make('slug')->searchable(),
                TextColumn::make('seo')->limit(40),
                TextColumn::make('value')
                    ->label('Цвет')
                    ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(
                        '<span style="max-width:250px;white-space:normal;overflow-wrap:anywhere;word-break:break-word;text-align:center;display:inline-block;padding:4px 10px;background:'
                            . e($state)
                            . ';color:#f5f5f5;text-shadow:0 0 2px #000,-1px -1px 0 #000,1px -1px 0 #000,-1px 1px 0 #000,1px 1px 0 #000">'
                            . e($state)
                            . '</span>'
                    )),
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
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
        ];
    }
}
