<?php

namespace App\Filament\Resources\Products\Products\Tables;

use App\Enums\CurrencyCode;
use App\Enums\Product\ProductLabel;
use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
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
                TextInputColumn::make('price')
                    ->label('Цена')
                    ->suffix(CurrencyCode::BYN->value),
                TextInputColumn::make('old_price')
                    ->label('Старая цена')
                    ->suffix(CurrencyCode::BYN->value),
                TextColumn::make('category.title')
                    ->label('Категория'),
                TextColumn::make('brand.name')
                    ->label('Бренд'),
                TextColumn::make('manufacturer.name')
                    ->label('Фабрика'),
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
                SelectFilter::make('category')
                    ->label('Категория')
                    ->relationship('category', 'title', function (Builder $query) {
                        $query->whereNotNull('parent_id')->whereNull('deleted_at')->orderBy('order');
                    })
                    ->query(function (Builder $query, array $state) {
                        $categories = [];
                        foreach ($state['values'] ?? [] as $categoryId) {
                            $categories = array_merge($categories, Category::getChildrenCategoriesIdsList($categoryId));
                        }
                        $query->when($categories, function (Builder $query, $categories) {
                            $query->whereIn('category_id', $categories);
                        });
                    })
                    ->multiple(),
                SelectFilter::make('manufacturer')
                    ->label('Фабрика')
                    ->relationship('manufacturer', 'name')
                    ->multiple(),
                SelectFilter::make('label_id')
                    ->label('Статус')
                    ->options(ProductLabel::class)
                    ->multiple(),
                TrashedFilter::make()->native(false)->default(true),
                Filter::make('more_five_sizes')
                    ->label('5 и более размеров')
                    ->query(function (Builder $query) {
                        $productIds = DB::table('product_attributes')
                            ->select('product_id', DB::raw('COUNT(*) as size_count'))
                            ->where('attribute_type', Size::class)
                            ->groupBy('product_id')
                            ->having('size_count', '>=', 5)
                            ->pluck('product_id');
                        $query->whereIn('id', $productIds->toArray());
                    }),
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
