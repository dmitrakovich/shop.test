<?php

namespace App\Filament\Resources\Ads\Banners;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Ads\Banners\Pages\CreateBanner;
use App\Filament\Resources\Ads\Banners\Pages\EditBanner;
use App\Filament\Resources\Ads\Banners\Pages\ListBanners;
use App\Filament\Resources\Ads\Banners\Schemas\BannerForm;
use App\Filament\Resources\Ads\Banners\Tables\BannersTable;
use App\Models\Ads\Banner;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Promo;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Баннер';

    protected static ?string $pluralModelLabel = 'Баннеры';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBanners::route('/'),
            'create' => CreateBanner::route('/create'),
            'edit' => EditBanner::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
