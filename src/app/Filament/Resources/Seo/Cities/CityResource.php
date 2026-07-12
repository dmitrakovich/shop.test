<?php

namespace App\Filament\Resources\Seo\Cities;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Seo\Cities\Pages\CreateCity;
use App\Filament\Resources\Seo\Cities\Pages\EditCity;
use App\Filament\Resources\Seo\Cities\Pages\ListCities;
use App\Filament\Resources\Seo\Cities\Schemas\CityForm;
use App\Filament\Resources\Seo\Cities\Tables\CitiesTable;
use App\Models\City;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CityResource extends Resource
{
    protected static ?string $model = City::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Seo;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Города';

    protected static ?string $modelLabel = 'Город';

    protected static ?string $pluralModelLabel = 'Города';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
            'create' => CreateCity::route('/create'),
            'edit' => EditCity::route('/{record}/edit'),
        ];
    }
}
