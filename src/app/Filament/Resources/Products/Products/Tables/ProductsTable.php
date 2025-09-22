<?php

namespace App\Filament\Resources\Products\Products\Tables;

use App\Enums\CurrencyCode;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Фото')
                    ->conversion('thumb')
                    ->imageHeight(75)
                    ->limit(1),
                IconColumn::make('deleted_at')
                    ->label('Опубликован')
                    ->getStateUsing(fn (Product $record) => !$record->trashed())
                    ->boolean()
                    ->alignCenter(),
                TextColumn::make('sku')
                    ->label('Артикул')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->money(CurrencyCode::BYN)
                    ->sortable(),
                TextColumn::make('old_price')
                    ->label('Старая цена')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->label('Категория')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->searchable(),
                TextColumn::make('color_txt')
                    ->label('Цвет')
                    ->searchable(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()->default(true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
