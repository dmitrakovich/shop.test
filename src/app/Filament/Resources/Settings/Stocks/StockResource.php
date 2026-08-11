<?php

namespace App\Filament\Resources\Settings\Stocks;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Settings\Stocks\Pages\CreateStock;
use App\Filament\Resources\Settings\Stocks\Pages\EditStock;
use App\Filament\Resources\Settings\Stocks\Pages\ListStocks;
use App\Filament\Resources\Settings\Stocks\Schemas\StockForm;
use App\Filament\Resources\Settings\Stocks\Tables\StocksTable;
use App\Models\Stock;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

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
        return StockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StocksTable::configure($table);
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
