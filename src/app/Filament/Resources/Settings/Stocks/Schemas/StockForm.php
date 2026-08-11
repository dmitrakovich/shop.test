<?php

namespace App\Filament\Resources\Settings\Stocks\Schemas;

use App\Enums\StockType;
use App\Models\Bots\Telegram\TelegramChat;
use App\Models\City;
use App\Models\Stock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основное')
                    ->schema([
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
                        Toggle::make('is_active')
                            ->label('Активен на сайте')
                            ->helperText('После создания включать и отключать склад нужно кнопкой в шапке страницы или в списке — так сразу пересчитается каталог.')
                            ->default(true)
                            ->visibleOn('create'),
                        TextEntry::make('is_active')
                            ->label('Статус на сайте')
                            ->state(fn (?Stock $record): string => match (true) {
                                $record === null => '—',
                                $record->is_active => 'Активен — виден на сайте и в ПВЗ',
                                default => 'Отключён — скрыт с сайта и из ПВЗ',
                            })
                            ->helperText('Чтобы изменить статус с пересчётом каталога, нажмите «Отключить» или «Включить» в шапке страницы.')
                            ->visibleOn('edit'),
                    ])
                    ->columns(2),
                Section::make('Адрес и контакты')
                    ->schema([
                        TextInput::make('address')
                            ->label('Адрес')
                            ->maxLength(255)
                            ->columnSpanFull(),
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
                            ->label('Широта')
                            ->numeric(),
                        TextInput::make('geo_longitude')
                            ->label('Долгота')
                            ->numeric(),
                    ])
                    ->columns(2),
                Section::make('Уведомления')
                    ->schema([
                        Select::make('private_chat_id')
                            ->label('Личный чат')
                            ->options(fn (): array => TelegramChat::query()->pluck('name', 'id')->all())
                            ->searchable()
                            ->nullable()
                            ->native(false),
                        Select::make('group_chat_id')
                            ->label('Групповой чат')
                            ->options(fn (): array => TelegramChat::query()->pluck('name', 'id')->all())
                            ->searchable()
                            ->nullable()
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Настройки')
                    ->schema([
                        Toggle::make('check_availability')
                            ->label('Сверка наличия с 1С'),
                    ]),
                Section::make('Фото')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('photos')
                            ->label('Фото магазина')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
