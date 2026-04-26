<?php

namespace App\Filament\Resources\ProductAttributes;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\ProductAttributes\Pages\CreateBrand;
use App\Filament\Resources\ProductAttributes\Pages\EditBrand;
use App\Filament\Resources\ProductAttributes\Pages\ListBrands;
use App\Models\Brand;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Бренд';

    protected static ?string $pluralModelLabel = 'Бренды';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('one_c_id')
                    ->label('ID в 1С')
                    ->numeric()
                    ->minValue(1),
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
                TextColumn::make('seo')->limit(40),
            ])
            ->defaultPaginationPageOption(100)
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }
}
