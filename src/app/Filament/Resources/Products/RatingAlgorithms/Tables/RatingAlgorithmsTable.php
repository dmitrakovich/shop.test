<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Tables;

use App\Enums\Product\RatingFactor;
use App\Models\RatingAlgorithm;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatingAlgorithmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                ...self::coefficientColumns(),
                TextColumn::make('updated_at')
                    ->label('Дата обновления')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (RatingAlgorithm $record): bool => !$record->isUsedInRatingConfig()),
            ])
            ->defaultSort('id', 'desc');
    }

    /**
     * @return list<TextColumn>
     */
    private static function coefficientColumns(): array
    {
        return array_map(
            fn (RatingFactor $factor): TextColumn => TextColumn::make($factor->coefficientColumn())
                ->label($factor->getLabel())
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: !in_array($factor, [
                    RatingFactor::Views,
                    RatingFactor::Carts,
                    RatingFactor::Purchases,
                    RatingFactor::Price,
                    RatingFactor::Discount,
                    RatingFactor::Season,
                    RatingFactor::CreatedAt,
                ], true)),
            RatingFactor::cases()
        );
    }
}
