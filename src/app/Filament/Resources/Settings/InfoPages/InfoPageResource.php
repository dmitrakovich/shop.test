<?php

namespace App\Filament\Resources\Settings\InfoPages;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Settings\InfoPages\Pages\CreateInfoPage;
use App\Filament\Resources\Settings\InfoPages\Pages\EditInfoPage;
use App\Filament\Resources\Settings\InfoPages\Pages\ListInfoPages;
use App\Filament\Resources\Settings\InfoPages\Schemas\InfoPageForm;
use App\Filament\Resources\Settings\InfoPages\Tables\InfoPagesTable;
use App\Models\InfoPage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InfoPageResource extends Resource
{
    protected static ?string $model = InfoPage::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Settings;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Статические страницы';

    protected static ?string $modelLabel = 'Статическая страница';

    protected static ?string $pluralModelLabel = 'Статические страницы';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'settings/info-pages';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return InfoPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfoPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfoPages::route('/'),
            'create' => CreateInfoPage::route('/create'),
            'edit' => EditInfoPage::route('/{record}/edit'),
        ];
    }
}
