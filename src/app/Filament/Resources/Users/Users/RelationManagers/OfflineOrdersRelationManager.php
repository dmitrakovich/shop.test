<?php

namespace App\Filament\Resources\Users\Users\RelationManagers;

use App\Filament\Resources\Orders\OfflineOrders\Tables\OfflineOrdersTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfflineOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'offlineOrders';

    protected static ?string $title = 'Покупки в магазинах';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return OfflineOrdersTable::configure($table, withClientColumns: false)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(OfflineOrdersTable::EAGER_LOAD));
    }
}
