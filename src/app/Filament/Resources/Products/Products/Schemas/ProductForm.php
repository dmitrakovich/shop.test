<?php

namespace App\Filament\Resources\Products\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('sku')
                                    ->label('Артикул')
                                    ->required(),
                                TextInput::make('slug')
                                    ->default('temp_slug_' . time())
                                    ->disabled(),
                            ])
                            ->columns(2),

                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('one_c_id')
                                    ->numeric(),
                            ])
                            ->columns(2),

                    ])
                    ->columnSpan(['lg' => 1]),

                TextInput::make('label_id')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('buy_price')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('old_price')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('category_id')
                    ->relationship('category', 'title')
                    ->required()
                    ->default(0),
                Select::make('season_id')
                    ->relationship('season', 'name')
                    ->required()
                    ->default(0),
                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->required()
                    ->default(0),
                Select::make('manufacturer_id')
                    ->relationship('manufacturer', 'name')
                    ->required()
                    ->default(0),
                Select::make('collection_id')
                    ->relationship('collection', 'name')
                    ->required()
                    ->default(0),
                TextInput::make('color_txt'),
                TextInput::make('fabric_top_txt'),
                TextInput::make('fabric_inner_txt'),
                TextInput::make('fabric_insole_txt'),
                TextInput::make('fabric_outsole_txt'),
                TextInput::make('heel_txt'),
                TextInput::make('bootleg_height_txt'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('action')
                    ->required(),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('product_group_id')
                    ->relationship('productGroup', 'id'),
                TextInput::make('product_features'),
                TextInput::make('key_features'),
                Select::make('country_of_origin_id')
                    ->relationship('countryOfOrigin', 'name'),
            ])
            ->columns(3);
    }
}
