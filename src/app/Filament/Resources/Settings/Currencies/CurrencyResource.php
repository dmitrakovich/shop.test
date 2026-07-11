<?php

namespace App\Filament\Resources\Settings\Currencies;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Settings\Currencies\Pages\CreateCurrency;
use App\Filament\Resources\Settings\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Settings\Currencies\Pages\ListCurrencies;
use App\Models\Currency;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CurrencyResource extends Resource
{
    protected static ?string $model = Currency::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static ?string $modelLabel = 'Валюта';

    protected static ?string $pluralModelLabel = 'Валюты';

    protected static ?string $navigationLabel = 'Курсы валют';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $slug = 'settings/currencies';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Код валюты')
                    ->required()
                    ->length(3)
                    ->unique(ignoreRecord: true)
                    ->disabledOn('edit')
                    ->dehydrated(),
                TextInput::make('country')
                    ->label('Код страны')
                    ->required()
                    ->length(2),
                TextInput::make('rate')
                    ->label('Курс')
                    ->required()
                    ->numeric(),
                TextInput::make('decimals')
                    ->label('Кол-во отображаемых знаков после запятой')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('symbol')
                    ->label('Знак')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Код валюты')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country')
                    ->label('Код страны')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Курс')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('decimals')
                    ->label('Знаков после запятой')
                    ->sortable(),
                TextColumn::make('symbol')
                    ->label('Знак'),
            ])
            ->defaultSort('code', 'asc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}
