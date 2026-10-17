<?php

namespace App\Filament\Resources\Orders\OrderItems;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Orders\OrderItems\Pages\ListOrderItems;
use App\Filament\Resources\Orders\OrderItems\Tables\OrderItemsTable;
use App\Models\Orders\OrderItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static ?string $slug = 'order-items';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Orders;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $navigationLabel = 'Товары в заказах';

    protected static ?string $modelLabel = 'Товар в заказе';

    protected static ?string $pluralModelLabel = 'Товары в заказах';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return OrderItemsTable::configure($table);
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
        return parent::getEloquentQuery()->with(OrderItemsTable::EAGER_LOAD);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderItems::route('/'),
        ];
    }
}
