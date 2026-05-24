<?php

namespace App\Filament\Resources\Products\RatingAlgorithms;

use App\Enums\Filament\NavGroup;
use App\Filament\Resources\Products\RatingAlgorithms\Pages\CreateRatingAlgorithm;
use App\Filament\Resources\Products\RatingAlgorithms\Pages\EditRatingAlgorithm;
use App\Filament\Resources\Products\RatingAlgorithms\Pages\ListRatingAlgorithms;
use App\Filament\Resources\Products\RatingAlgorithms\Schemas\RatingAlgorithmForm;
use App\Filament\Resources\Products\RatingAlgorithms\Tables\RatingAlgorithmsTable;
use App\Models\RatingAlgorithm;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RatingAlgorithmResource extends Resource
{
    protected static ?string $model = RatingAlgorithm::class;

    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Products;

    protected static ?string $modelLabel = 'Алгоритм рейтинга';

    protected static ?string $pluralModelLabel = 'Алгоритмы рейтинга';

    protected static ?int $navigationSort = 16;

    public static function form(Schema $schema): Schema
    {
        return RatingAlgorithmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RatingAlgorithmsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatingAlgorithms::route('/'),
            'create' => CreateRatingAlgorithm::route('/create'),
            'edit' => EditRatingAlgorithm::route('/{record}/edit'),
        ];
    }
}
