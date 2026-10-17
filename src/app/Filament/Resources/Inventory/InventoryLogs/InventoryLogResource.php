<?php

namespace App\Filament\Resources\Inventory\InventoryLogs;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Inventory\InventoryLogs\Pages\ListInventoryLogs;
use App\Filament\Resources\Inventory\InventoryLogs\Tables\InventoryLogsTable;
use App\Models\Logs\InventoryLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryLogResource extends Resource
{
    protected static ?string $model = InventoryLog::class;

    protected static ?string $slug = 'inventory/logs';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Inventory;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Лог наличия';

    protected static ?string $modelLabel = 'Запись лога наличия';

    protected static ?string $pluralModelLabel = 'Лог наличия';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return InventoryLogsTable::configure($table);
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
        return parent::getEloquentQuery()->with(['product.media']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInventoryLogs::route('/'),
        ];
    }
}
