<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
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
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('old_slug')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('label_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('buy_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('old_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('category.title')
                    ->searchable(),
                TextColumn::make('season.name')
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->searchable(),
                TextColumn::make('manufacturer.name')
                    ->searchable(),
                TextColumn::make('collection.name')
                    ->searchable(),
                TextColumn::make('color_txt')
                    ->searchable(),
                TextColumn::make('fabric_top_txt')
                    ->searchable(),
                TextColumn::make('fabric_inner_txt')
                    ->searchable(),
                TextColumn::make('fabric_insole_txt')
                    ->searchable(),
                TextColumn::make('fabric_outsole_txt')
                    ->searchable(),
                TextColumn::make('heel_txt')
                    ->searchable(),
                TextColumn::make('bootleg_height_txt')
                    ->searchable(),
                IconColumn::make('action')
                    ->boolean(),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('productGroup.id')
                    ->searchable(),
                TextColumn::make('product_features')
                    ->searchable(),
                TextColumn::make('key_features')
                    ->searchable(),
                TextColumn::make('countryOfOrigin.name')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
