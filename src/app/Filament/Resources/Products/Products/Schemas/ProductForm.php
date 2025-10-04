<?php

namespace App\Filament\Resources\Products\Products\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                                    ->readOnly(),
                                RichEditor::make('description')
                                    ->label('Описание')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Section::make('Цены')
                            ->schema([
                                TextInput::make('price')
                                    ->required()
                                    ->label('Цена')
                                    ->numeric()
                                    ->default(0.0)
                                    ->prefix('BYN'),
                                TextInput::make('old_price')
                                    ->label('Старая цена')
                                    ->required()
                                    ->numeric()
                                    ->default(0.0)
                                    ->prefix('BYN'),
                            ])
                            ->columns(2),
                        Section::make('Фото')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->image()
                                    ->multiple()
                                    ->panelLayout('grid')
                                    ->reorderable()
                                    ->downloadable()
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Характеристики')
                            ->schema([
                                // TextInput::make('one_c_id'),
                                // TextInput::make('rating'),
                                Select::make('category_id')
                                    ->label('Категория')
                                    ->relationship('category', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('brand_id')
                                    ->label('Бренд')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('manufacturer_id')
                                    ->label('Производитель')
                                    ->relationship('manufacturer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('sizes')
                                    ->label('Размеры')
                                    ->multiple()
                                    ->relationship('sizes', 'name', function (Builder $query) {
                                        $query->where('is_active', true);
                                    })
                                    ->preload()
                                    ->required(),
                                Select::make('collection_id')
                                    ->label('Коллекция')
                                    ->relationship('collection', 'name', function (Builder $query) {
                                        $query->orderByDesc('id');
                                    })
                                    ->native(false)
                                    ->required(),
                                Select::make('season_id')
                                    ->label('Сезон')
                                    ->relationship('season', 'name')
                                    ->required()
                                    ->native(false),
                                Select::make('colors')
                                    ->label('Цвет для фильтра')
                                    ->multiple()
                                    ->relationship('colors', 'name')
                                    ->preload(),
                                Select::make('fabrics')
                                    ->label('Материал для фильтра')
                                    ->multiple()
                                    ->relationship('fabrics', 'name')
                                    ->preload(),
                                Select::make('styles')
                                    ->label('Стиль')
                                    ->multiple()
                                    ->relationship('styles', 'name')
                                    ->preload(),
                                Select::make('heels')
                                    ->label('Тип каблука/подошвы')
                                    ->multiple()
                                    ->relationship('heels', 'name')
                                    ->preload(),
                                Select::make('country_of_origin_id')
                                    ->label('Страна производитель')
                                    ->relationship('countryOfOrigin', 'name')
                                    ->native(false),
                            ]),
                        Section::make('Описание')
                            ->schema([
                                TextInput::make('color_txt')->label('Цвет'),
                                TextInput::make('fabric_top_txt')->label('Материал верха'),
                                TextInput::make('fabric_inner_txt')->label('Материал внутри'),
                                TextInput::make('fabric_insole_txt')->label('Материал стельки'),
                                TextInput::make('fabric_outsole_txt')->label('Материал подошвы'),
                                TextInput::make('bootleg_height_txt')->label('Высота голенища'),
                                TextInput::make('heel_txt')->label('Высота каблука/подошвы'),
                            ]),

                    ])
                    ->columnSpan(['lg' => 1]),

                // todo:
                TextInput::make('label_id')
                    ->required()
                    ->numeric(),
                Select::make('product_group_id')
                    ->relationship('productGroup', 'id'),
                TextInput::make('product_features'),
                TextInput::make('key_features'),

            ])
            ->columns(3);
    }
}
