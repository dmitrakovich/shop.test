<?php

namespace App\Filament\Resources\Products\Products\RelationManagers;

use App\Filament\Resources\Inventory\InventoryLogs\Tables\InventoryLogsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class InventoryLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryLogs';

    protected static ?string $title = 'История наличия';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return InventoryLogsTable::configure($table, withProductColumns: false);
    }
}
