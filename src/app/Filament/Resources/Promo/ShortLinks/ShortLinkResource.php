<?php

namespace App\Filament\Resources\Promo\ShortLinks;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Promo\ShortLinks\Pages\ListShortLinks;
use App\Filament\Resources\Promo\ShortLinks\Tables\ShortLinksTable;
use App\Models\ShortLink;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ShortLinkResource extends Resource
{
    protected static ?string $model = ShortLink::class;

    protected static ?string $slug = 'short-links';

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Promo;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Короткие ссылки';

    protected static ?string $modelLabel = 'Короткая ссылка';

    protected static ?string $pluralModelLabel = 'Короткие ссылки';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return ShortLinksTable::configure($table);
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

    public static function getPages(): array
    {
        return [
            'index' => ListShortLinks::route('/'),
        ];
    }
}
