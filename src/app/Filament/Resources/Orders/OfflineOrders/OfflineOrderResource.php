<?php

namespace App\Filament\Resources\Orders\OfflineOrders;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Orders\OfflineOrders\Pages\ListOfflineOrders;
use App\Filament\Resources\Orders\OfflineOrders\Tables\OfflineOrdersTable;
use App\Models\Orders\OfflineOrder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OfflineOrderResource extends Resource
{
    protected static ?string $model = OfflineOrder::class;

    protected static ?string $slug = 'offline-orders';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Orders;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Оффлайн продажи';

    protected static ?string $modelLabel = 'Оффлайн продажа';

    protected static ?string $pluralModelLabel = 'Оффлайн продажи';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return OfflineOrdersTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(OfflineOrdersTable::EAGER_LOAD);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOfflineOrders::route('/'),
        ];
    }
}
