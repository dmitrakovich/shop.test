<?php

namespace App\Filament\Resources\Settings\Stocks;

use App\Enums\Filament\NavGroup;
use App\Enums\StockType;
use App\Filament\Actions\ToggleStockActiveAction;
use App\Filament\Resources\Settings\Stocks\Pages\CreateStock;
use App\Filament\Resources\Settings\Stocks\Pages\EditStock;
use App\Filament\Resources\Settings\Stocks\Pages\ListStocks;
use App\Models\Bots\Telegram\TelegramChat;
use App\Models\City;
use App\Models\Stock;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $modelLabel = 'Склад / магазин';

    protected static ?string $pluralModelLabel = 'Склады / магазины';

    protected static ?string $navigationLabel = 'Склады / магазины';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $slug = 'settings/stocks';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('one_c_id')
                    ->label('ID в 1C')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->label('Тип')
                    ->options(StockType::class)
                    ->required()
                    ->native(false),
                Select::make('city_id')
                    ->label('Город')
                    ->options(fn (): array => City::query()->pluck('name', 'id')->all())
                    ->required()
                    ->searchable()
                    ->native(false),
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(50),
                TextInput::make('internal_name')
                    ->label('Внутреннее название')
                    ->required()
                    ->maxLength(50),
                Select::make('private_chat_id')
                    ->label('Личный чат для уведомлений')
                    ->options(fn (): array => TelegramChat::query()->pluck('name', 'id')->all())
                    ->searchable()
                    ->nullable()
                    ->native(false),
                Select::make('group_chat_id')
                    ->label('Групповой чат для уведомлений')
                    ->options(fn (): array => TelegramChat::query()->pluck('name', 'id')->all())
                    ->searchable()
                    ->nullable()
                    ->native(false),
                TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(255),
                TextInput::make('address_zip')
                    ->label('Почтовый индекс')
                    ->maxLength(10),
                TextInput::make('worktime')
                    ->label('Время работы')
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('contact_person')
                    ->label('Контактное лицо')
                    ->maxLength(255),
                TextInput::make('geo_latitude')
                    ->label('Координаты (широта)')
                    ->numeric(),
                TextInput::make('geo_longitude')
                    ->label('Координаты (долгота)')
                    ->numeric(),
                Toggle::make('check_availability')
                    ->label('Сверка наличия')
                    ->default(false),
                Toggle::make('is_active')
                    ->label('Активен')
                    ->helperText('Для включения/отключения с пересчётом каталога используйте действие в списке.')
                    ->default(true)
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                SpatieMediaLibraryFileUpload::make('photos')
                    ->label('Фото магазина')
                    ->multiple()
                    ->reorderable()
                    ->image(),
                TextInput::make('site_sorting')
                    ->label('Сортировка на сайте')
                    ->numeric()
                    ->integer()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge(),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('internal_name')
                    ->label('Внутреннее название')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('Город')
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Адрес')
                    ->wrap()
                    ->toggleable(),
                IconColumn::make('check_availability')
                    ->label('Сверка')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextInputColumn::make('site_sorting')
                    ->label('Сортировка')
                    ->rules(['integer']),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Тип')
                    ->options(StockType::class),
                Filter::make('is_active')
                    ->label('Скрыть неактивные')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)),
            ])
            ->defaultPaginationPageOption(50)
            ->recordActions([
                ToggleStockActiveAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
            'create' => CreateStock::route('/create'),
            'edit' => EditStock::route('/{record}/edit'),
        ];
    }
}
