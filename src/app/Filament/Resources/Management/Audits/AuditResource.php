<?php

namespace App\Filament\Resources\Management\Audits;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Management\Audits\Pages\ListAudits;
use App\Filament\Resources\Management\Audits\Schemas\AuditInfolist;
use App\Filament\Resources\Management\Audits\Tables\AuditsTable;
use App\Models\Audit;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static ?string $slug = 'audits';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Management;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Аудит';

    protected static ?string $modelLabel = 'Запись аудита';

    protected static ?string $pluralModelLabel = 'Аудит';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditsTable::configure($table);
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
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
        ];
    }
}
