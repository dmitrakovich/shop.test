<?php

namespace App\Filament\Resources\OrdersDistribution\Logs;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\OrdersDistribution\Logs\Pages\ListOrderDistributionLogs;
use App\Filament\Resources\OrdersDistribution\Logs\Tables\LogsTable;
use App\Models\Logs\OrderDistributionLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderDistributionLogResource extends Resource
{
    protected static ?string $model = OrderDistributionLog::class;

    protected static ?string $slug = 'orders-distribution/logs';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::OrdersDistribution;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Лог';

    protected static ?string $modelLabel = 'Запись лога';

    protected static ?string $pluralModelLabel = 'Лог распределения';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return LogsTable::configure($table);
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
        return parent::getEloquentQuery()->with(['admin']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderDistributionLogs::route('/'),
        ];
    }
}
