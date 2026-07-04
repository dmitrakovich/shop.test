<?php

namespace App\Filament\Resources\Seo\SeoPages;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Seo\SeoPages\Pages\CreateSeoPage;
use App\Filament\Resources\Seo\SeoPages\Pages\EditSeoPage;
use App\Filament\Resources\Seo\SeoPages\Pages\ListSeoPages;
use App\Filament\Resources\Seo\SeoPages\Schemas\SeoPageForm;
use App\Filament\Resources\Seo\SeoPages\Tables\SeoPagesTable;
use App\Models\Seo\SeoPage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Seo;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'SEO-страницы';

    protected static ?string $modelLabel = 'SEO-страница';

    protected static ?string $pluralModelLabel = 'SEO-страницы';

    protected static ?string $recordTitleAttribute = 'url';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SeoPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeoPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoPages::route('/'),
            'create' => CreateSeoPage::route('/create'),
            'edit' => EditSeoPage::route('/{record}/edit'),
        ];
    }
}
