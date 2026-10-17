<?php

namespace App\Filament\Resources\Orders\OrderItemStatusLogs;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Orders\OrderItemStatusLogs\Pages\ListOrderItemStatusLogs;
use App\Filament\Resources\Orders\OrderItemStatusLogs\Tables\OrderItemStatusLogsTable;
use App\Models\Logs\OrderItemStatusLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemStatusLogResource extends Resource
{
    protected static ?string $model = OrderItemStatusLog::class;

    protected static ?string $slug = 'order-item-movements';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Orders;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Движение единиц';

    protected static ?string $modelLabel = 'Движение единицы';

    protected static ?string $pluralModelLabel = 'Движение единиц';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return OrderItemStatusLogsTable::configure($table);
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

    /**
     * "Out of stock" units are soft deleted, so they are included.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['orderItem.product.brand', 'orderItem.size', 'stock']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderItemStatusLogs::route('/'),
        ];
    }
}
